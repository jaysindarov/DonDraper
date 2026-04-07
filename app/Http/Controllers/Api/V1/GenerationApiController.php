<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessImageGeneration;
use App\Jobs\ProcessVideoGeneration;
use App\Models\Generation;
use Illuminate\Http\Request;

class GenerationApiController extends Controller
{
    public function index(Request $request)
    {
        $generations = $request->user()
            ->generations()
            ->latest()
            ->paginate(20);

        return response()->json($generations);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'            => 'required|in:image,video',
            'prompt'          => 'required|string|max:2000',
            'negative_prompt' => 'nullable|string|max:1000',
            'attributes'      => 'nullable|array',
        ]);

        $user       = $request->user();
        $isVideo    = $validated['type'] === 'video';
        $creditCost = $isVideo ? 5 : 1;

        if ($user->credits < $creditCost) {
            return response()->json([
                'message' => "Insufficient credits. This generation costs {$creditCost} credits.",
            ], 422);
        }

        $attributes = $validated['attributes'] ?? [];
        $model      = $attributes['model'] ?? ($isVideo ? 'veo-3.1' : 'gpt-image-1');

        $generation = $user->generations()->create([
            'type'           => $validated['type'],
            'prompt'         => $validated['prompt'],
            'negative_prompt' => $validated['negative_prompt'] ?? null,
            'model'          => $model,
            'provider'       => $isVideo ? 'google' : 'openai',
            'attributes'     => $attributes,
            'status'         => 'pending',
            'credits_used'   => $creditCost,
        ]);

        $user->deductCredits($creditCost);

        if ($isVideo) {
            ProcessVideoGeneration::dispatch($generation)->onQueue('video');
        } else {
            ProcessImageGeneration::dispatch($generation);
        }

        return response()->json($generation, 201);
    }

    public function show(Request $request, Generation $generation)
    {
        if ($generation->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($generation);
    }
}
