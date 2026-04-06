<?php

declare(strict_types=1);

namespace App\Services\VideoGeneration;

use App\Contracts\VideoGenerationProvider;
use App\DTO\VideoGenerationStatus;
use App\Models\Generation;
use Illuminate\Support\Facades\Http;

final class OpenAiSoraProvider implements VideoGenerationProvider
{
    private const BASE_URL = 'https://api.openai.com/v1';

    public function providerKey(): string
    {
        return 'openai';
    }

    public function submit(Generation $generation, string $prompt): string
    {
        $attributes = $generation->attributes ?? [];

        $response = Http::withToken(config('services.openai.key'))
            ->timeout(30)
            ->post(self::BASE_URL . '/video/generations', [
                'model'    => $generation->model ?? 'sora-1.0-hd',
                'prompt'   => $prompt,
                'size'     => $this->resolveResolution($attributes['resolution'] ?? '1080p'),
                'duration' => (int) ($attributes['duration'] ?? 5),
            ]);

        if ($response->status() === 400) {
            throw new VideoGenerationException(
                'Sora rejected request: ' . ($response->json('error.message') ?? $response->body())
            );
        }

        if ($response->failed()) {
            throw new \RuntimeException('Sora submit failed: ' . $response->body());
        }

        return $response->json('id');
    }

    public function poll(string $jobId): VideoGenerationStatus
    {
        $response = Http::withToken(config('services.openai.key'))
            ->timeout(15)
            ->get(self::BASE_URL . "/video/generations/{$jobId}");

        if ($response->failed()) {
            return VideoGenerationStatus::failed('Sora poll error: ' . $response->body());
        }

        return match ($response->json('status')) {
            'completed' => VideoGenerationStatus::completed($response->json('data.0.url')),
            'failed'    => VideoGenerationStatus::failed($response->json('error.message') ?? 'Unknown error'),
            default     => VideoGenerationStatus::processing(),
        };
    }

    private function resolveResolution(string $requested): string
    {
        return match ($requested) {
            '480p'  => '480p',
            '720p'  => '720p',
            '1080p' => '1080p',
            default => '1080p',
        };
    }
}
