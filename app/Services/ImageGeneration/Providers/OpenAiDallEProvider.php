<?php

namespace App\Services\ImageGeneration\Providers;

use App\Contracts\ImageGenerationProvider;
use App\Exceptions\NonRetryableException;
use App\Models\Generation;
use App\Services\ImageGeneration\GenerationResult;
use App\Services\ImageGeneration\ImageStorageService;
use Illuminate\Support\Facades\Http;

/**
 * Handles DALL-E 2 and DALL-E 3 image generation.
 *
 * Both models use the generations endpoint and return a temporary URL;
 * the image is downloaded and stored locally before returning.
 */
class OpenAiDallEProvider implements ImageGenerationProvider
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

        if ($response->status() === 400) {
            throw new NonRetryableException(
                'OpenAI rejected request: ' . ($response->json('error.message') ?? $response->body())
            );
        }

        if ($response->status() === 429 || $response->status() >= 500) {
            throw new \RuntimeException('OpenAI temporary error (' . $response->status() . ')');
        }

        if ($response->failed()) {
            throw new NonRetryableException('DALL-E error: ' . $response->body());
        }

        $url = $response->json('data.0.url');

        if (empty($url)) {
            throw new NonRetryableException('DALL-E returned empty URL');
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
