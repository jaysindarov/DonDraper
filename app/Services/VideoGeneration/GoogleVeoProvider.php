<?php

declare(strict_types=1);

namespace App\Services\VideoGeneration;

use App\Contracts\VideoGenerationProvider;
use App\DTO\VideoGenerationStatus;
use App\Models\Generation;
use Illuminate\Support\Facades\Http;

/**
 * Google Veo 3.1 via the Gemini Developer API (AI Studio).
 *
 * Uses API key auth — simpler than Vertex AI service accounts.
 * Docs: https://ai.google.dev/api/generate-content#video
 *
 * For Vertex AI instead: swap BASE_URL to
 *   https://{location}-aiplatform.googleapis.com/v1/projects/{project}/...
 * and use OAuth2 bearer token.
 */
final class GoogleVeoProvider implements VideoGenerationProvider
{
    private const BASE_URL = 'https://generativelanguage.googleapis.com/v1beta';

    public function providerKey(): string
    {
        return 'google';
    }

    public function submit(Generation $generation, string $prompt): string
    {
        $attributes = $generation->attributes ?? [];
        $model      = $generation->model ?? 'veo-3.1';

        $response = Http::withQueryParameters(['key' => config('services.google.api_key')])
            ->withHeaders(['Content-Type' => 'application/json'])
            ->timeout(30)
            ->post(self::BASE_URL . "/models/{$model}:generateVideo", [
                'prompt' => [
                    'text' => $prompt,
                ],
                'generationConfig' => [
                    'durationSeconds' => (int) ($attributes['duration'] ?? 5),
                    'aspectRatio'     => $attributes['aspect_ratio'] ?? '16:9',
                    'resolution'      => $attributes['resolution'] ?? '1080p',
                    'numberOfVideos'  => 1,
                ],
            ]);

        if ($response->status() === 400) {
            throw new VideoGenerationException(
                'Veo rejected request: ' . ($response->json('error.message') ?? $response->body())
            );
        }

        if ($response->failed()) {
            throw new \RuntimeException('Veo submit failed: ' . $response->body());
        }

        // Returns a long-running operation name: "operations/xxx"
        $operationName = $response->json('name');

        if (empty($operationName)) {
            throw new VideoGenerationException('Veo returned no operation name');
        }

        return $operationName;
    }

    public function poll(string $jobId): VideoGenerationStatus
    {
        // jobId is the full operation name e.g. "operations/abc123"
        $response = Http::withQueryParameters(['key' => config('services.google.api_key')])
            ->timeout(15)
            ->get(self::BASE_URL . "/{$jobId}");

        if ($response->failed()) {
            return VideoGenerationStatus::failed('Veo poll error: ' . $response->body());
        }

        if (!$response->json('done')) {
            return VideoGenerationStatus::processing();
        }

        if ($response->json('error')) {
            return VideoGenerationStatus::failed(
                $response->json('error.message') ?? 'Veo generation failed'
            );
        }

        // Extract video URI from operation response
        $videoUri = $response->json('response.generatedVideos.0.video.uri')
            ?? $response->json('response.videos.0.uri');

        if (empty($videoUri)) {
            return VideoGenerationStatus::failed('Veo returned no video URI');
        }

        return VideoGenerationStatus::completed($videoUri);
    }
}
