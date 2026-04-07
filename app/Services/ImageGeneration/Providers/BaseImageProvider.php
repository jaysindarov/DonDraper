<?php

namespace App\Services\ImageGeneration\Providers;

use App\Contracts\ImageGenerationProvider;
use App\Exceptions\NonRetryableException;
use Illuminate\Http\Client\Response;

/**
 * Shared HTTP error handling for all image generation providers.
 *
 * 400 → non-retryable (bad prompt/params, retrying won't help).
 * 429/5xx → retryable (rate limit or provider outage).
 * Other failures → non-retryable (unexpected — escalate immediately).
 */
abstract class BaseImageProvider implements ImageGenerationProvider
{
    /**
     * Assert the response is successful; throw the appropriate exception if not.
     * Call this immediately after every HTTP request before reading the body.
     */
    protected function assertHttpSuccess(Response $response, string $context): void
    {
        if ($response->status() === 400) {
            throw new NonRetryableException(
                "{$context} rejected request: " . ($response->json('error.message') ?? $response->body())
            );
        }

        if ($response->status() === 429 || $response->status() >= 500) {
            throw new \RuntimeException("{$context} temporary error ({$response->status()})");
        }

        if ($response->failed()) {
            throw new NonRetryableException("{$context} error: " . $response->body());
        }
    }
}
