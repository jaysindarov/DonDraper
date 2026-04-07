<?php

namespace App\Services\ImageGeneration\Providers;

use App\Models\Generation;
use App\Services\ImageGeneration\GenerationResult;
use App\Services\ImageGeneration\ImageStorageService;
use Illuminate\Support\Facades\Http;

/**
 * Google Imagen image generation provider.
 *
 * API credentials: GOOGLE_AI_API_KEY must be set in your .env file.
 * The model slug is read from config/ai_models.php under 'google_model'.
 */
class GoogleImageProvider extends BaseImageProvider
{
    private const API_BASE = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct(
        private readonly ImageStorageService $storage,
    ) {}

    public function isAsync(): bool
    {
        return false;
    }

    public function generate(Generation $generation, string $prompt, array $modelConfig): GenerationResult
    {
        $googleModel = $modelConfig['google_model']
            ?? throw new \InvalidArgumentException('Missing google_model in model config.');

        $apiKey = config('services.google.api_key')
            ?? throw new \RuntimeException('GOOGLE_AI_API_KEY is not configured.');

        $response = Http::withHeaders([
                'Content-Type'   => 'application/json',
                'X-Goog-Api-Key' => $apiKey,
            ])
            ->timeout(120)
            ->post(self::API_BASE . "/models/{$googleModel}:generateImages", [
                'prompt'              => ['text' => $prompt],
                'number_of_images'    => 1,
                'aspect_ratio'        => $this->resolveAspectRatio($modelConfig),
                'safety_filter_level' => 'block_some',
                'person_generation'   => 'allow_adult',
            ]);

        $this->assertHttpSuccess($response, "Google AI ({$googleModel})");

        $b64 = $response->json('generatedImages.0.image.imageBytes');

        if (empty($b64)) {
            throw new NonRetryableException("Google AI returned empty result for model {$googleModel}");
        }

        $localUrl = $this->storage->storeFromBase64($generation->id, $b64);

        return GenerationResult::completed($localUrl);
    }

    public function poll(Generation $generation, string $predictionId): GenerationResult
    {
        throw new \LogicException('GoogleImageProvider is synchronous and does not support polling.');
    }

    private function resolveAspectRatio(array $modelConfig): string
    {
        return $modelConfig['defaults']['aspect_ratio'] ?? '1:1';
    }
}
