<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessImageGeneration;
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
        $attributes = GenerationAttribute::where('is_active', true)
            ->where('applicable_to', 'image')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');

        return Inertia::render('Generations/Create', [
            'attributes' => $attributes,
            'credits' => auth()->user()->credits,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'              => 'required|in:image,video',
            'prompt'            => 'required|string|max:2000',
            'negative_prompt'   => 'nullable|string|max:1000',
            'attributes'        => 'nullable|array',
            'product_type'      => 'nullable|string|max:100',
            'product_image'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'person_1_name'     => 'nullable|string|max:100',
            'person_1_image'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'person_2_name'     => 'nullable|string|max:100',
            'person_2_image'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $user = auth()->user();

        if ($user->credits < 1) {
            return back()->withErrors(['credits' => 'Insufficient credits. Please upgrade your plan.']);
        }

        // Store product image
        $productImagePath = null;
        if ($request->hasFile('product_image')) {
            $productImagePath = $request->file('product_image')
                ->store("products/{$user->id}", 'public');
        }

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

        $generation = $user->generations()->create([
            'type'              => $validated['type'],
            'prompt'            => $validated['prompt'],
            'negative_prompt'   => $validated['negative_prompt'] ?? null,
            'product_type'      => $validated['product_type'] ?? null,
            'product_image_path'=> $productImagePath,
            'reference_persons' => !empty($referencePersons) ? $referencePersons : null,
            'model'             => $validated['attributes']['model'] ?? 'gpt-image-1',
            'provider'          => 'openai',
            'attributes'        => $validated['attributes'] ?? [],
            'status'            => 'pending',
            'credits_used'      => 1,
        ]);

        $user->deductCredits(1);

        ProcessImageGeneration::dispatch($generation);

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

    public function destroy(Generation $generation)
    {
        //$this->authorize('delete', $generation);

        if ($generation->product_image_path) {
            Storage::disk('public')->delete($generation->product_image_path);
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
