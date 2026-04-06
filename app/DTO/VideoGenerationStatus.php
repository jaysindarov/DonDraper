<?php

declare(strict_types=1);

namespace App\DTO;

readonly class VideoGenerationStatus
{
    public function __construct(
        public string $state,       // 'pending' | 'processing' | 'completed' | 'failed'
        public ?string $videoUrl = null,
        public ?string $error = null,
    ) {}

    public static function pending(): self
    {
        return new self('pending');
    }

    public static function processing(): self
    {
        return new self('processing');
    }

    public static function completed(string $videoUrl): self
    {
        return new self('completed', videoUrl: $videoUrl);
    }

    public static function failed(string $error): self
    {
        return new self('failed', error: $error);
    }

    public function isInProgress(): bool
    {
        return in_array($this->state, ['pending', 'processing'], strict: true);
    }

    public function isCompleted(): bool
    {
        return $this->state === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->state === 'failed';
    }
}
