<?php

namespace App\Http\Controllers;

use App\Enums\VideoProvider;
use App\Jobs\ProcessImageGeneration;
use App\Jobs\ProcessVideoGeneration;
use App\Models\Generation;
use App\Models\GenerationAttribute;
use Illuminate\Http\Request;
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
        $imageAttributes = GenerationAttribute::where('is_active', true)
            ->whereIn('applicable_to', ['image', 'both'])
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');

        $videoAttributes = GenerationAttribute::where('is_active', true)
            ->whereIn('applicable_to', ['video', 'both'])
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');

        return Inertia::render('Generations/Create', [
            'imageAttributes' => $imageAttributes,
            'videoAttributes' => $videoAttributes,
            'credits'         => auth()->user()->credits,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'               => 'required|in:image,video',
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

        if ($user->credits < $creditCost) {
            return back()->withErrors([
                'credits' => "Insufficient credits. This generation costs {$creditCost} credits.",
            ]);
        }

        // Store product images (multiple angles, up to 4)
        $productImagePaths = [];
        if ($request->hasFile('product_images')) {
            foreach ($request->file('product_images') as $file) {
                $productImagePaths[] = $file->store("products/{$user->id}", 'public');
            }
        }
        $productImagePath = $productImagePaths[0] ?? null; // primary for display / backward compat

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

        // Determine provider from the selected model
        $model    = $attributes['model'] ?? ($isVideo ? 'veo-3.1' : 'gpt-image-1');
        $provider = $isVideo
            ? VideoProvider::fromModel($model)->value
            : 'openai';

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
        ]);

        $user->deductCredits($creditCost);

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
        //$this->authorize('view', $generation);

        if (!$generation->result_url) {
            abort(404, 'Image not ready yet.');
        }

        $relativePath = ltrim(parse_url($generation->result_url, PHP_URL_PATH), '/');
        $diskPath = preg_replace('#^storage/#', '', $relativePath);

        if (Storage::disk('public')->exists($diskPath)) {
            return Storage::disk('public')->download(
                $diskPath,
                "dondraper-{$generation->id}.png"
            );
        }

        // Fallback: proxy external URL (old records before local storage)
        $content = Http::timeout(30)->get($generation->result_url)->body();

        return response($content, 200, [
            'Content-Type'        => 'image/png',
            'Content-Disposition' => "attachment; filename=\"dondraper-{$generation->id}.png\"",
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
        //$this->authorize('delete', $generation);

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
