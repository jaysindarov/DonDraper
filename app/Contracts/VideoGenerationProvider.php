<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTO\VideoGenerationStatus;
use App\Models\Generation;

interface VideoGenerationProvider
{
    /**
     * Submit the video generation request to the provider.
     * Returns a provider-specific job/operation ID used for polling.
     */
    public function submit(Generation $generation, string $prompt): string;

    /**
     * Poll the provider for the current status of a submitted job.
     */
    public function poll(string $jobId): VideoGenerationStatus;

    /**
     * The provider key this implementation handles (e.g. 'openai', 'elevenlabs', 'google').
     */
    public function providerKey(): string;
}
