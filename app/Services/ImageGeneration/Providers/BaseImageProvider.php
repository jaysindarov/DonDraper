<?php

namespace App\Services\ImageGeneration\Providers;

use App\Contracts\ImageGenerationProvider;
use App\Exceptions\NonRetryableException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;

/**
 * Shared HTTP error handling for all image generation providers.
 *
 * Raw HTTP path (assertHttpSuccess):
 *   400  → NonRetryableException  (bad prompt / params; retrying won't help)
 *   429  → RuntimeException       (rate limit; queue will retry)
 *   5xx  → RuntimeException       (provider outage; queue will retry)
 *   Other failures → NonRetryableException
 *
 * Laravel AI SDK path (handleRequestException):
 *   Same semantics, mapped from the SDK's RequestException.
 *   FailoverableException (RateLimitedException, ProviderOverloadedException)
 *   propagates as-is so the SDK's built-in failover logic can engage before
 *   the queue retry kicks in.
 */
abstract class BaseImageProvider implements ImageGenerationProvider
{
    /**
     * Assert the response is successful; throw the appropriate exception if not.
     * Call this immediately after every raw HTTP request before reading the body.
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

    /**
     * Map a Laravel HTTP RequestException (thrown by the AI SDK) to our exception types.
     *
     * 400  → NonRetryableException  (content policy, bad params)
     * 429  → RuntimeException       (rate limit; let the queue retry)
     * 5xx  → RuntimeException       (transient provider outage; let the queue retry)
     *
     * @throws NonRetryableException
     * @throws \RuntimeException
     * @return never
     */
    protected function handleRequestException(RequestException $e, string $context): never
    {
        $status = $e->response?->status() ?? 0;

        if ($status === 400) {
            $message = $e->response?->json('error.message') ?? $e->getMessage();
            throw new NonRetryableException("{$context} rejected request: {$message}");
        }

        if ($status === 429 || $status >= 500) {
            throw new \RuntimeException("{$context} temporary error ({$status})", previous: $e);
        }

        throw new NonRetryableException("{$context} error: " . ($e->response?->body() ?? $e->getMessage()));
    }
}
