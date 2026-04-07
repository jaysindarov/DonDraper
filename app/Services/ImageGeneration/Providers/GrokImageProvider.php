<?php

namespace App\Services\ImageGeneration\Providers;

use App\Models\Generation;
use App\Services\ImageGeneration\GenerationResult;
use App\Services\ImageGeneration\ImageStorageService;
use Illuminate\Support\Facades\Http;

/**
 * xAI Grok image generation provider.
 *
 * OpenAI-compatible endpoint hosted by xAI.
 * API credentials: GROK_API_KEY must be set in your .env file.
 * Docs: https://docs.x.ai/api/image-generation
 */
class GrokImageProvider extends BaseImageProvider
{
    private const API_BASE = 'https://api.x.ai/v1';

    public function __construct(
        private readonly ImageStorageService $storage,
    ) {}

    public function isAsync(): bool
    {
        return false;
    }

    public function generate(Generation $generation, string $prompt, array $modelConfig): GenerationResult
    {
        $grokModel = $modelConfig['grok_model']
            ?? throw new \InvalidArgumentException('Missing grok_model in model config.');

        $apiKey = config('services.grok.key')
            ?? throw new \RuntimeException('GROK_API_KEY is not configured.');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
        ])->timeout(120)->post(self::API_BASE . '/images/generations', [
            'model'           => $grokModel,
            'prompt'          => $prompt,
            'n'               => 1,
            'response_format' => 'b64_json',
        ]);

        $this->assertHttpSuccess($response, "Grok ({$grokModel})");

        $b64 = $response->json('data.0.b64_json');

        if (empty($b64)) {
            throw new NonRetryableException("Grok ({$grokModel}) returned empty result");
        }

        $localUrl = $this->storage->storeFromBase64($generation->id, $b64);

        return GenerationResult::completed($localUrl);
    }

    public function poll(Generation $generation, string $predictionId): GenerationResult
    {
        throw new \LogicException('GrokImageProvider is synchronous and does not support polling.');
    }
}
