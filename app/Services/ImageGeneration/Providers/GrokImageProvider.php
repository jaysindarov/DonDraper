<?php

namespace App\Services\ImageGeneration\Providers;

use App\Exceptions\NonRetryableException;
use App\Models\Generation;
use App\Services\ImageGeneration\GenerationResult;
use App\Services\ImageGeneration\ImageStorageService;
use Illuminate\Http\Client\RequestException;
use Laravel\Ai\Image;

/**
 * xAI Grok image generation provider.
 *
 * Delegates the HTTP transport to the Laravel AI SDK (xai driver) so all
 * retry logic, rate-limit handling, and credential resolution happen in one
 * place. The model slug and API key are read from config/ai.php (xai driver)
 * which maps to the GROK_API_KEY env var — the same key used by services.grok.
 *
 * Docs: https://docs.x.ai/api/image-generation
 */
class GrokImageProvider extends BaseImageProvider
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
        $grokModel = $modelConfig['grok_model']
            ?? throw new \InvalidArgumentException('Missing grok_model in model config.');

        try {
            $response = Image::of($prompt)
                ->timeout(120)
                ->generate('xai', $grokModel);
        } catch (RequestException $e) {
            $this->handleRequestException($e, "Grok ({$grokModel})");
        }

        $b64 = $response->firstImage()->image;

        if (empty($b64)) {
            throw new NonRetryableException("Grok ({$grokModel}) returned an empty result.");
        }

        return GenerationResult::completed(
            $this->storage->storeFromBase64($generation->id, $b64)
        );
    }

    public function poll(Generation $generation, string $predictionId): GenerationResult
    {
        throw new \LogicException('GrokImageProvider is synchronous and does not support polling.');
    }
}
