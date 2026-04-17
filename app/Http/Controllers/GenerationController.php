<?php

namespace App\Http\Controllers;

use App\Enums\ImageProvider;
use App\Enums\VideoProvider;
use App\Jobs\ProcessImageGeneration;
use App\Jobs\ProcessVideoGeneration;
use App\Models\Generation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class GenerationController extends Controller
{
    public function index(): Response
    {
        $generations = auth()->user()
            ->generations()
            ->latest()
            ->paginate(20);

        return Inertia::render('Generations/Index', [
            'generations' => $generations,
        ]);
    }

    public function create(): Response
    {
        $imageModels = collect(config('ai_models'))
            ->except('video_models')
            ->map(fn ($cfg, $id) => [
                'id'          => $id,
                'label'       => $cfg['label'],
                'provider'    => $cfg['provider'],
                'description' => $cfg['description'],
                'recommended' => $cfg['recommended'] ?? false,
            ])
            ->values();

        $videoModels = collect(config('ai_models.video_models'))
            ->map(fn ($cfg, $id) => [
                'id'          => $id,
                'label'       => $cfg['label'],
                'provider'    => $cfg['provider'],
                'description' => $cfg['description'],
                'recommended' => $cfg['recommended'] ?? false,
            ])
            ->values();

        return Inertia::render('Generations/Create', [
            'credits'     => auth()->user()->credits,
            'imageModels' => $imageModels,
            'videoModels' => $videoModels,
        ]);
    }

    public function store(Request $request)
    {
        $validImageModels = array_keys(array_diff_key(config('ai_models'), ['video_models' => true]));
        $validVideoModels = array_keys(config('ai_models.video_models'));

        $validated = $request->validate([
            'type'               => 'required|in:image,video',
            'model'              => 'nullable|string',
            'prompt'             => 'required|string|max:2000',
            'negative_prompt'    => 'nullable|string|max:1000',
            'attributes'         => 'nullable|array',
            'product_type'       => 'nullable|string|max:100',
            'product_images'     => 'nullable|array|max:4',
            'product_images.*'   => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'person_1_name'      => 'nullable|string|max:100',
            'person_1_image'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'person_2_name'      => 'nullable|string|max:100',
            'person_2_image'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $user        = auth()->user();
        $isVideo     = $validated['type'] === 'video';
        $creditCost  = $isVideo ? 5 : 1;

        // Store product images (multiple angles, up to 4)
        $productImagePaths = [];
        if ($request->hasFile('product_images')) {
            foreach ($request->file('product_images') as $file) {
                $productImagePaths[] = $file->store("products/{$user->id}", 'public');
            }
        }
        $productImagePath = $productImagePaths[0] ?? null;

        // Store reference person images
        $referencePersons = [];
        foreach ([1, 2] as $n) {
            if ($request->hasFile("person_{$n}_image")) {
                $path = $request->file("person_{$n}_image")
                    ->store("persons/{$user->id}", 'public');
                $referencePersons[] = [
                    'path' => $path,
                    'name' => $validated["person_{$n}_name"] ?? "Person {$n}",
                ];
            }
        }

        $attributes = $validated['attributes'] ?? [];

        // Resolve and validate the selected model
        $requestedModel = $validated['model'] ?? null;
        if ($isVideo) {
            $model = in_array($requestedModel, $validVideoModels) ? $requestedModel : 'veo-3.1';
        } else {
            $model = in_array($requestedModel, $validImageModels) ? $requestedModel : 'gpt-image-1';
        }

        $provider = $isVideo
            ? VideoProvider::fromModel($model)->value
            : ImageProvider::fromModel($model)->value;

        // Atomic credit deduction + generation creation
        try {

            $generation = DB::transaction(function () use ($user, $validated, $productImagePath, $productImagePaths, $referencePersons, $model, $provider, $attributes, $creditCost, $isVideo) {
                // Lock the user row and refresh credits
                $user = User::where('id', $user->id)->lockForUpdate()->firstOrFail();
                $effectiveCredits = $user->current_team_id
                    ? ($user->currentTeam?->credits ?? 0)
                    : $user->credits;

                if ($effectiveCredits < $creditCost) {
                    throw new \RuntimeException("Insufficient credits. This generation costs {$creditCost} credits.");
                }

                $generation = $user->generations()->create([
                    'type'               => $validated['type'],
                    'prompt'             => $validated['prompt'],
                    'negative_prompt'    => $validated['negative_prompt'] ?? null,
                    'product_type'       => $validated['product_type'] ?? null,
                    'product_image_path'  => $productImagePath,
                    'product_image_paths' => !empty($productImagePaths) ? $productImagePaths : null,
                    'reference_persons'  => !empty($referencePersons) ? $referencePersons : null,
                    'model'              => $model,
                    'provider'           => $provider,
                    'attributes'         => $attributes,
                    'status'             => 'pending',
                    'credits_used'       => $creditCost,
                    'team_id'            => $user->current_team_id,
                ]);

                // Deduct from team or user
                if ($user->current_team_id && $user->currentTeam) {
                    $user->currentTeam->deductCredits($creditCost);
                } else {
                    $user->deductCredits($creditCost);
                }
                dd($generation);
                return $generation;
            }, attempts: 3);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['credits' => $e->getMessage()]);
        }

        if ($isVideo) {
            ProcessVideoGeneration::dispatch($generation)->onQueue('video');
        } else {
            ProcessImageGeneration::dispatch($generation);
        }

        return redirect()->route('generations.show', $generation)
            ->with('success', 'Generation started! Your image will be ready shortly.');
    }

    public function show(Generation $generation): Response
    {
        //$this->authorize('view', $generation);

        return Inertia::render('Generations/Show', [
            'generation' => $generation,
        ]);
    }

    public function download(Generation $generation)
    {
        $this->authorize('view', $generation);

        if (!$generation->result_url) {
            abort(404, 'Image not ready yet.');
        }

        $ext = $generation->type === 'video' ? 'mp4' : 'png';
        $filename = "dondraper-{$generation->id}-{$generation->created_at->format('Y-m-d')}.{$ext}";

        $relativePath = ltrim(parse_url($generation->result_url, PHP_URL_PATH), '/');
        $diskPath = preg_replace('#^storage/#', '', $relativePath);

        if (Storage::disk('public')->exists($diskPath)) {
            return Storage::disk('public')->download($diskPath, $filename);
        }

        // Fallback: proxy external URL — only allow trusted origins
        $allowedHosts = array_filter([
            parse_url(config('app.url'), PHP_URL_HOST),
            parse_url(config('services.openai.base_url', ''), PHP_URL_HOST),
            'oaidalleapiprodscus.blob.core.windows.net',
        ]);

        $urlHost = parse_url($generation->result_url, PHP_URL_HOST);
        if (!$urlHost || !in_array($urlHost, $allowedHosts, true)) {
            abort(403, 'Download not available for this resource.');
        }

        $response = Http::timeout(30)->get($generation->result_url);

        if ($response->failed()) {
            abort(502, 'Failed to retrieve the file from the provider.');
        }

        $contentType = $generation->type === 'video' ? 'video/mp4' : 'image/png';

        return response($response->body(), 200, [
            'Content-Type'        => $contentType,
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function togglePublic(Generation $generation)
    {
        //$this->authorize('update', $generation);

        $generation->update(['is_public' => !$generation->is_public]);

        return back()->with('success', $generation->is_public ? 'Added to public gallery.' : 'Removed from public gallery.');
    }

    public function destroy(Generation $generation)
    {
        $this->authorize('delete', $generation);

        foreach ($generation->allProductImagePaths() as $path) {
            Storage::disk('public')->delete($path);
        }

        foreach ($generation->reference_persons ?? [] as $person) {
            if (!empty($person['path'])) {
                Storage::disk('public')->delete($person['path']);
            }
        }

        if ($generation->result_url) {
            $relativePath = ltrim(parse_url($generation->result_url, PHP_URL_PATH), '/');
            $diskPath = preg_replace('#^storage/#', '', $relativePath);
            if (Storage::disk('public')->exists($diskPath)) {
                Storage::disk('public')->delete($diskPath);
            }
        }

        $generation->delete();

        return redirect()->route('generations.index')
            ->with('success', 'Generation deleted.');
    }
}
