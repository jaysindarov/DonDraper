<?php

namespace App\Services\ImageGeneration\Providers;

use App\Exceptions\NonRetryableException;
use App\Models\Generation;
use App\Services\ImageGeneration\GenerationResult;
use App\Services\ImageGeneration\ImageStorageService;
use Illuminate\Support\Facades\Http;

/**
 * Handles DALL-E 3 image generation via the OpenAI generations endpoint.
 * Returns a temporary URL which is downloaded and stored locally.
 */
class OpenAiDallEProvider extends BaseImageProvider
{
    public function __construct(
        private readonly ImageStorageService $storage,
    ) {}

    public function isAsync(): bool
    {
        return false;
    }

    public function generate(Generation $generation, string $prompt, array $modelConfig): GenerationResult
    {
        $model      = $generation->model;
        $attributes = $generation->attributes ?? [];

        $payload = [
            'model'           => $model,
            'prompt'          => $prompt,
            'n'               => 1,
            'size'            => $this->resolveSize($model, $attributes, $modelConfig),
            'response_format' => 'url',
        ];

        if ($model === 'dall-e-3') {
            $defaults           = $modelConfig['defaults'] ?? [];
            $payload['quality'] = ($attributes['quality'] ?? '') === 'standard' ? 'standard' : ($defaults['quality'] ?? 'hd');
            $payload['style']   = $this->resolveStyle($attributes, $defaults);
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.openai.key'),
            'Content-Type'  => 'application/json',
        ])->timeout(90)->post(config('services.openai.base_url') . '/images/generations', $payload);

        $this->assertHttpSuccess($response, "DALL-E ({$model})");

        $url = $response->json('data.0.url');

        if (empty($url)) {
            throw new NonRetryableException("DALL-E ({$model}) returned empty URL");
        }

        $localUrl = $this->storage->storeFromUrl($generation->id, $url);

        return GenerationResult::completed($localUrl);
    }

    public function poll(Generation $generation, string $predictionId): GenerationResult
    {
        throw new \LogicException('OpenAiDallEProvider is synchronous and does not support polling.');
    }

    private function resolveSize(string $model, array $attributes, array $modelConfig): string
    {
        $requested = $attributes['resolution'] ?? null;
        $valid      = $modelConfig['sizes'] ?? [];
        $default    = $modelConfig['defaults']['size'] ?? '1024x1024';

        return ($requested && in_array($requested, $valid)) ? $requested : $default;
    }

    /**
     * Map UI art_style values to DALL-E 3 style param ('vivid' or 'natural').
     * Natural styles: photorealistic, watercolor, sketch, minimalist, impressionist, oil_painting.
     * Vivid styles: everything else (cinematic, cyberpunk, fantasy, neon, anime, comic, etc.).
     */
    private function resolveStyle(array $attributes, array $defaults): string
    {
        $naturalStyles = ['photorealistic', 'watercolor', 'sketch', 'minimalist', 'impressionist', 'oil_painting'];
        $artStyle      = $attributes['art_style'] ?? null;

        if ($artStyle && in_array($artStyle, $naturalStyles)) {
            return 'natural';
        }

        // If art_style is explicitly 'vivid' or 'natural' (legacy direct values), pass through
        if (in_array($artStyle, ['vivid', 'natural'])) {
            return $artStyle;
        }

        return $defaults['style'] ?? 'vivid';
    }
}
