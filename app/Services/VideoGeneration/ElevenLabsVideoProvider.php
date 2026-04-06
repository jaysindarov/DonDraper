<?php

declare(strict_types=1);

namespace App\Services\VideoGeneration;

use App\Contracts\VideoGenerationProvider;
use App\DTO\VideoGenerationStatus;
use App\Models\Generation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

final class ElevenLabsVideoProvider implements VideoGenerationProvider
{
    private const BASE_URL = 'https://api.elevenlabs.io/v1';

    public function providerKey(): string
    {
        return 'elevenlabs';
    }

    public function submit(Generation $generation, string $prompt): string
    {
        $attributes = $generation->attributes ?? [];

        $payload = [
            'prompt'       => $prompt,
            'model_id'     => $generation->model ?? 'eleven_video_v1',
            'duration'     => (int) ($attributes['duration'] ?? 5),
            'aspect_ratio' => $attributes['aspect_ratio'] ?? '16:9',
            'resolution'   => $attributes['resolution'] ?? '1080p',
        ];

        // If reference person images are provided, include them as style reference
        foreach ($generation->reference_persons ?? [] as $i => $person) {
            if (!empty($person['path']) && Storage::disk('public')->exists($person['path'])) {
                $payload['reference_image_' . ($i + 1)] = base64_encode(
                    Storage::disk('public')->get($person['path'])
                );
            }
        }

        $response = Http::withHeaders([
            'xi-api-key'   => config('services.elevenlabs.key'),
            'Content-Type' => 'application/json',
        ])->timeout(30)->post(self::BASE_URL . '/video/generations', $payload);

        if ($response->status() === 400 || $response->status() === 422) {
            throw new VideoGenerationException(
                'ElevenLabs rejected request: ' . ($response->json('detail') ?? $response->body())
            );
        }

        if ($response->failed()) {
            throw new \RuntimeException('ElevenLabs submit failed: ' . $response->body());
        }

        return $response->json('generation_id') ?? $response->json('id');
    }

    public function poll(string $jobId): VideoGenerationStatus
    {
        $response = Http::withHeaders(['xi-api-key' => config('services.elevenlabs.key')])
            ->timeout(15)
            ->get(self::BASE_URL . "/video/generations/{$jobId}");

        if ($response->failed()) {
            return VideoGenerationStatus::failed('ElevenLabs poll error: ' . $response->body());
        }

        $status = $response->json('status');

        return match ($status) {
            'completed', 'succeeded' => VideoGenerationStatus::completed(
                $response->json('video_url') ?? $response->json('output_url')
            ),
            'failed', 'error' => VideoGenerationStatus::failed(
                $response->json('error') ?? $response->json('detail') ?? 'Generation failed'
            ),
            default => VideoGenerationStatus::processing(),
        };
    }
}
