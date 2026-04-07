<?php

namespace App\Services\ImageGeneration\Providers;

use App\Contracts\ImageGenerationProvider;
use App\Exceptions\NonRetryableException;
use App\Models\Generation;
use App\Services\ImageGeneration\GenerationResult;
use App\Services\ImageGeneration\ImageStorageService;
use Illuminate\Support\Facades\Http;

/**
 * Handles image generation via Replicate's async prediction API.
 *
 * Flow:
 *   1. generate() — submits the prediction, returns GenerationResult::pending()
 *   2. The job stores the predictionId and releases itself back to the queue
 *   3. poll() — checks status; returns pending again or completed with a stored URL
 *
 * The replicate_model key in config/ai_models.php determines which model is used.
 * Version-pinned models use the format "owner/name:version".
 */
class ReplicateProvider implements ImageGenerationProvider
{
    private const API_BASE = 'https://api.replicate.com/v1';

    public function __construct(
        private readonly ImageStorageService $storage,
    ) {}

    public function isAsync(): bool
    {
        return true;
    }

    public function generate(Generation $generation, string $prompt, array $modelConfig): GenerationResult
    {
        $replicateModel = $modelConfig['replicate_model']
            ?? throw new NonRetryableException("No replicate_model defined in config for model \"{$generation->model}\".");

        $input = array_merge(
            ['prompt' => $prompt, 'num_outputs' => 1],
            $this->buildInputOptions($generation),
        );

        if (str_contains($replicateModel, ':')) {
            // Version-pinned: POST /v1/predictions with version hash
            [, $version] = explode(':', $replicateModel, 2);
            $response = Http::withToken(config('services.replicate.token'))
                ->post(self::API_BASE . '/predictions', [
                    'version' => $version,
                    'input'   => $input,
                ]);
        } else {
            // Latest: POST /v1/models/{owner}/{name}/predictions
            $response = Http::withToken(config('services.replicate.token'))
                ->post(self::API_BASE . "/models/{$replicateModel}/predictions", [
                    'input' => $input,
                ]);
        }

        if ($response->failed()) {
            throw new NonRetryableException('Replicate submit failed: ' . $response->body());
        }

        $predictionId = $response->json('id');

        if (!$predictionId) {
            throw new NonRetryableException('Replicate returned no prediction ID: ' . $response->body());
        }

        return GenerationResult::pending($predictionId);
    }

    public function poll(Generation $generation, string $predictionId): GenerationResult
    {
        $response = Http::withToken(config('services.replicate.token'))
            ->get(self::API_BASE . "/predictions/{$predictionId}");

        if ($response->failed()) {
            throw new \RuntimeException('Replicate poll failed: ' . $response->body());
        }

        $data   = $response->json();
        $status = $data['status'] ?? 'starting';

        if (in_array($status, ['starting', 'processing'])) {
            return GenerationResult::pending($predictionId);
        }

        if (in_array($status, ['failed', 'canceled'])) {
            throw new NonRetryableException($data['error'] ?? 'Replicate prediction failed.');
        }

        // succeeded
        $outputUrl = is_array($data['output']) ? $data['output'][0] : $data['output'];
        $localUrl  = $this->storage->storeFromUrl($generation->id, $outputUrl);

        return GenerationResult::completed($localUrl);
    }

    private function buildInputOptions(Generation $generation): array
    {
        $attrs   = $generation->attributes ?? [];
        $options = [];

        // Resolution → width / height
        if (isset($attrs['resolution'])) {
            $parts = explode('x', $attrs['resolution']);
            if (isset($parts[0]) && (int) $parts[0] > 0) {
                $options['width'] = (int) $parts[0];
            }
            if (isset($parts[1]) && (int) $parts[1] > 0) {
                $options['height'] = (int) $parts[1];
            }
        }

        // Inference steps (slider 1–50 in UI)
        if (isset($attrs['steps']) && (int) $attrs['steps'] > 0) {
            $options['num_inference_steps'] = (int) $attrs['steps'];
        }

        // Guidance scale (slider 1–20 in UI)
        if (isset($attrs['guidance_scale']) && (float) $attrs['guidance_scale'] > 0) {
            $options['guidance_scale'] = (float) $attrs['guidance_scale'];
        }

        // Seed (optional, for reproducibility)
        if (isset($attrs['seed']) && (int) $attrs['seed'] > 0) {
            $options['seed'] = (int) $attrs['seed'];
        }

        return $options;
    }
}
