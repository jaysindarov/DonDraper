<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\VideoGenerationProvider;
use App\Enums\VideoProvider;
use App\Models\Generation;
use App\Services\VideoGeneration\VideoGenerationException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Handles async video generation across OpenAI Sora, ElevenLabs, and Google Veo.
 *
 * Flow:
 *  1st attempt  → submit to provider → store operation_id in metadata → release(15)
 *  2nd+ attempt → poll provider     → if done: download + store | if pending: release(15)
 *
 * Uses release() (not sleep) so the worker is not blocked between polls.
 * retryUntil() enforces a 15-minute hard deadline.
 */
final class ProcessVideoGeneration implements ShouldQueue
{
    use Queueable;

    // Large tries limit — actual deadline is enforced by retryUntil()
    public int $tries = 200;

    // Per-attempt timeout (short, we're just doing HTTP calls)
    public int $timeout = 45;

    public function __construct(
        public readonly Generation $generation
    ) {}

    public function middleware(): array
    {
        return [(new WithoutOverlapping($this->generation->id))->expireAfter(900)];
    }

    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(15);
    }

    public function handle(VideoGenerationProvider ...$providers): void
    {
        $this->generation->refresh();

        if ($this->generation->status === 'completed') {
            return;
        }

        $provider = $this->resolveProvider($providers);
        $metadata = $this->generation->metadata ?? [];

        // ── First run: submit the job ──────────────────────────────────────────
        if (empty($metadata['video_job_id'])) {
            $this->generation->update(['status' => 'processing']);

            try {
                $prompt = $this->generation->prompt;
                $jobId  = $provider->submit($this->generation, $prompt);
            } catch (VideoGenerationException $e) {
                $this->markFailed($e->getMessage());
                $this->fail($e);
                return;
            } catch (\Throwable $e) {
                // Transient error — retry
                Log::warning('Video submit failed, will retry', [
                    'generation' => $this->generation->id,
                    'error'      => $e->getMessage(),
                ]);
                throw $e;
            }

            $this->generation->update([
                'metadata' => array_merge($metadata, [
                    'video_job_id' => $jobId,
                    'provider'     => $provider->providerKey(),
                    'submitted_at' => now()->toIso8601String(),
                ]),
            ]);

            // Come back in 15 seconds to check status
            $this->release(15);
            return;
        }

        // ── Subsequent runs: poll for completion ───────────────────────────────
        $jobId  = $metadata['video_job_id'];
        $status = $provider->poll($jobId);

        if ($status->isInProgress()) {
            $this->release(15);
            return;
        }

        if ($status->isFailed()) {
            $this->markFailed($status->error ?? 'Provider reported failure');
            $this->fail(new \RuntimeException($status->error ?? 'Video generation failed'));
            return;
        }

        // Completed — download video and store it permanently
        try {
            $localUrl = $this->storeVideo($status->videoUrl, $provider->providerKey());
        } catch (\Throwable $e) {
            $this->markFailed('Failed to download video: ' . $e->getMessage());
            $this->fail($e);
            return;
        }

        $this->generation->update([
            'status'     => 'completed',
            'result_url' => $localUrl,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function resolveProvider(array $providers): VideoGenerationProvider
    {
        $metadata     = $this->generation->metadata ?? [];
        $providerKey  = $metadata['provider'] ?? null;
        $enum         = $providerKey
            ? VideoProvider::from($providerKey)
            : VideoProvider::fromModel($this->generation->model ?? '');

        foreach ($providers as $provider) {
            if ($provider->providerKey() === $enum->value) {
                return $provider;
            }
        }

        // Fallback to first available
        return $providers[0] ?? throw new \RuntimeException('No video provider registered');
    }

    private function storeVideo(string $url, string $providerKey): string
    {
        // Google Veo returns a URI that may need an API key appended
        if ($providerKey === 'google' && !str_contains($url, '?')) {
            $url .= '?key=' . config('services.google.api_key');
        }

        $response = Http::withOptions(['stream' => true])->timeout(120)->get($url);

        if ($response->failed()) {
            throw new \RuntimeException("Failed to download video from provider (HTTP {$response->status()})");
        }

        $storagePath = "generations/{$this->generation->id}.mp4";
        Storage::disk('public')->put($storagePath, $response->body());

        return Storage::disk('public')->url($storagePath);
    }

    private function markFailed(string $message): void
    {
        try {
            $this->generation->update([
                'status'        => 'failed',
                'error_message' => substr($message, 0, 500),
            ]);
        } catch (\Throwable) {}
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessVideoGeneration permanently failed', [
            'generation_id' => $this->generation->id,
            'error'         => $exception->getMessage(),
        ]);

        $this->markFailed($exception->getMessage());
    }
}
