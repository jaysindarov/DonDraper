<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ReplicateImageProvider
{
    // Maps model key → Replicate model version string
    private const MODEL_VERSIONS = [
        'stable-diffusion-xl' => 'stability-ai/sdxl:39ed52f2a78e934b3ba6e2a89f5b1c712de7dfea535525255b1aa35c5565e08b',
        'stable-diffusion-3'  => 'stability-ai/stable-diffusion-3',
        'flux-pro'            => 'black-forest-labs/flux-pro',
        'flux-dev'            => 'black-forest-labs/flux-dev',
        'flux-schnell'        => 'black-forest-labs/flux-schnell',
    ];

    public function isReplicateModel(string $model): bool
    {
        return isset(self::MODEL_VERSIONS[$model]) || str_contains($model, '/');
    }

    /**
     * Submit prediction and return prediction ID.
     */
    public function submit(string $model, string $prompt, array $options = []): string
    {
        $modelPath = self::MODEL_VERSIONS[$model] ?? $model;

        // Some Replicate models use /models/{owner}/{name}/predictions, others use /predictions with version
        if (str_contains($modelPath, ':')) {
            // Version-pinned model
            [$modelRef, $version] = explode(':', $modelPath, 2);
            $response = Http::withToken(config('services.replicate.token'))
                ->post('https://api.replicate.com/v1/predictions', [
                    'version' => $version,
                    'input'   => array_merge([
                        'prompt' => $prompt,
                        'num_outputs' => 1,
                    ], $options),
                ]);
        } else {
            // Latest model (owner/name format)
            $response = Http::withToken(config('services.replicate.token'))
                ->post("https://api.replicate.com/v1/models/{$modelPath}/predictions", [
                    'input' => array_merge([
                        'prompt' => $prompt,
                        'num_outputs' => 1,
                    ], $options),
                ]);
        }

        if ($response->failed()) {
            throw new RuntimeException('Replicate submit failed: ' . $response->body());
        }

        $id = $response->json('id');

        if (!$id) {
            throw new RuntimeException('Replicate returned no prediction ID: ' . $response->body());
        }

        return $id;
    }

    /**
     * Poll prediction status. Returns ['status', 'url'] where url is set on success.
     */
    public function poll(string $predictionId): array
    {
        $response = Http::withToken(config('services.replicate.token'))
            ->get("https://api.replicate.com/v1/predictions/{$predictionId}");

        if ($response->failed()) {
            throw new RuntimeException('Replicate poll failed: ' . $response->body());
        }

        $data   = $response->json();
        $status = $data['status'] ?? 'starting';

        return match ($status) {
            'succeeded' => [
                'status' => 'completed',
                'url'    => is_array($data['output']) ? $data['output'][0] : $data['output'],
            ],
            'failed', 'canceled' => [
                'status' => 'failed',
                'error'  => $data['error'] ?? 'Replicate prediction failed.',
            ],
            default => ['status' => 'processing'],
        };
    }
}
