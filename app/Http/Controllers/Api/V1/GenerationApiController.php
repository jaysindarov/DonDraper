<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ImageProvider;
use App\Enums\VideoProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreGenerationRequest;
use App\Jobs\ProcessImageGeneration;
use App\Jobs\ProcessVideoGeneration;
use App\Models\Generation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GenerationApiController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->generations()->latest()->paginate(20)
        );
    }

    public function store(StoreGenerationRequest $request)
    {
        $validated  = $request->validated();
        $user       = $request->user();
        $isVideo    = $validated['type'] === 'video';
        $creditCost = $isVideo ? 5 : 1;

        // Resolve model
        $validImageModels = array_keys(array_diff_key(config('ai_models'), ['video_models' => true]));
        $validVideoModels = array_keys(config('ai_models.video_models'));

        $requestedModel = $validated['model'] ?? null;
        if ($isVideo) {
            $model = in_array($requestedModel, $validVideoModels) ? $requestedModel : 'veo-3.1';
        } else {
            $model = in_array($requestedModel, $validImageModels) ? $requestedModel : 'gpt-image-1';
        }

        $provider = $isVideo
            ? VideoProvider::fromModel($model)->value
            : ImageProvider::fromModel($model)->value;

        try {
            $generation = DB::transaction(function () use ($user, $validated, $model, $provider, $creditCost, $isVideo) {
                $user = $user->lockForUpdate()->refresh();

                $effectiveCredits = $user->current_team_id
                    ? ($user->currentTeam?->credits ?? 0)
                    : $user->credits;

                if ($effectiveCredits < $creditCost) {
                    throw new \RuntimeException("Insufficient credits. This generation costs {$creditCost} credits.");
                }

                $generation = $user->generations()->create([
                    'type'            => $validated['type'],
                    'prompt'          => $validated['prompt'],
                    'negative_prompt' => $validated['negative_prompt'] ?? null,
                    'product_type'    => $validated['product_type'] ?? null,
                    'model'           => $model,
                    'provider'        => $provider,
                    'attributes'      => $validated['attributes'] ?? [],
                    'status'          => 'pending',
                    'credits_used'    => $creditCost,
                    'team_id'         => $user->current_team_id,
                ]);

                if ($user->current_team_id && $user->currentTeam) {
                    $user->currentTeam->deductCredits($creditCost);
                } else {
                    $user->deductCredits($creditCost);
                }

                return $generation;
            }, attempts: 3);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

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
