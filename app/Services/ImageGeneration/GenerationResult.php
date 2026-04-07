<?php

namespace App\Services\ImageGeneration;

/**
 * Immutable value object representing the outcome of a generation attempt.
 *
 * A result is either:
 *  - completed: the image is stored and localUrl is the public URL
 *  - pending:   async submission succeeded; predictionId must be polled later
 */
final class GenerationResult
{
    private function __construct(
        public readonly bool $isPending,
        public readonly ?string $localUrl = null,
        public readonly ?string $predictionId = null,
    ) {}

    public static function completed(string $localUrl): self
    {
        return new self(isPending: false, localUrl: $localUrl);
    }

    public static function pending(string $predictionId): self
    {
        return new self(isPending: true, predictionId: $predictionId);
    }

    public function isCompleted(): bool
    {
        return !$this->isPending;
    }
}
