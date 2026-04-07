<?php

namespace App\Contracts;

use App\Models\Generation;
use App\Services\ImageGeneration\GenerationResult;

/**
 * Contract for all image generation providers.
 *
 * Sync providers (OpenAI): generate() returns a completed GenerationResult.
 * Async providers (Replicate): generate() returns a pending GenerationResult
 * with a predictionId; the job polls via poll() until completion.
 */
interface ImageGenerationProvider
{
    /**
     * Submit or execute an image generation.
     *
     * @param  array<string, mixed>  $modelConfig  Entry from config/ai_models.php
     */
    public function generate(Generation $generation, string $prompt, array $modelConfig): GenerationResult;

    /**
     * Poll an async prediction for its result.
     * Sync providers should throw \LogicException if called.
     */
    public function poll(Generation $generation, string $predictionId): GenerationResult;

    /**
     * Whether this provider operates asynchronously (submit → poll cycle).
     */
    public function isAsync(): bool;
}
