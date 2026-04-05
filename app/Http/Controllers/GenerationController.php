<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessImageGeneration;
use App\Models\Generation;
use App\Models\GenerationAttribute;
use Illuminate\Http\Request;
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
            'type' => 'required|in:image,video',
            'prompt' => 'required|string|max:2000',
            'negative_prompt' => 'nullable|string|max:1000',
            'model' => 'nullable|string',
            'attributes' => 'nullable|array',
        ]);

        $user = auth()->user();

        if ($user->credits < 1) {
            return back()->withErrors(['credits' => 'Insufficient credits. Please upgrade your plan.']);
        }

        $generation = $user->generations()->create([
            'type' => $validated['type'],
            'prompt' => $validated['prompt'],
            'negative_prompt' => $validated['negative_prompt'] ?? null,
            'model' => $validated['attributes']['model'] ?? 'dall-e-3',
            'provider' => 'openai',
            'attributes' => $validated['attributes'] ?? [],
            'status' => 'pending',
            'credits_used' => 1,
        ]);

        $user->deductCredits(1);

        ProcessImageGeneration::dispatch($generation);

        return redirect()->route('generations.show', $generation)
            ->with('success', 'Generation started! Your image will be ready shortly.');
    }

    public function show(Generation $generation): Response
    {
        $this->authorize('view', $generation);

        return Inertia::render('Generations/Show', [
            'generation' => $generation,
        ]);
    }

    public function destroy(Generation $generation)
    {
        $this->authorize('delete', $generation);
        $generation->delete();

        return redirect()->route('generations.index')
            ->with('success', 'Generation deleted.');
    }
}
