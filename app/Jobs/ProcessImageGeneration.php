<?php

namespace App\Jobs;

use App\Exceptions\NonRetryableException;
use App\Models\Generation;
use App\Services\ImageGeneration\ImageProviderFactory;
use App\Services\ImageGeneration\PromptBuilder;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

/**
 * Orchestrates image generation regardless of the underlying AI provider.
 *
 * Sync providers (OpenAI): single run, completes immediately.
 * Async providers (Replicate): first run submits, subsequent runs poll;
 * the job releases itself back to the queue until the prediction finishes.
 *
 * Steps are recorded into metadata.steps so the UI can render
 * a live progress timeline while the job is running.
 *
 * To add a new AI model, register it in config/ai_models.php only.
 */
class ProcessImageGeneration implements ShouldQueue
{
    use Queueable;

    // High tries + retryUntil supports async polling for up to 10 minutes
    public int $tries   = 100;
    public int $timeout = 120;
    public int $backoff = 10;

    public function __construct(
        public readonly Generation $generation,
    ) {}

    public function retryUntil(): CarbonInterface
    {
        return now()->addMinutes(10);
    }

    public function middleware(): array
    {
        return [new WithoutOverlapping($this->generation->id)];
    }

    public function handle(ImageProviderFactory $factory, PromptBuilder $promptBuilder): void
    {
        $this->generation->refresh();

        if ($this->generation->status === 'completed') {
            return;
        }

        $model    = $this->generation->model;
        $config   = $factory->configFor($model);
        $provider = $factory->make($model);

        // ── Async polling path ────────────────────────────────────────────────
        $predictionId = $this->generation->metadata['prediction_id'] ?? null;

        if ($provider->isAsync() && $predictionId) {
            $this->recordStep('AI is processing your request...', 'running');

            try {
                $result = $provider->poll($this->generation, $predictionId);
            } catch (NonRetryableException $e) {
                $this->failStep();
                $this->markFailed($e->getMessage());
                $this->fail($e);
                return;
            } catch (\Throwable) {
                $this->release(5);
                return;
            }

            if ($result->isPending) {
                $this->release(5);
                return;
            }

            $this->completeStep();
            $this->markCompleted($result->localUrl);
            return;
        }

        // ── First run: build prompt and generate ──────────────────────────────
        $this->generation->update(['status' => 'processing']);
        $this->clearSteps();
        $this->recordStep('Job started', 'done');

        // Analyze reference photos if provided (expensive: GPT-4o Vision calls)
        $hasPersons     = !empty($this->generation->reference_persons);
        $productPaths   = $this->generation->allProductImagePaths();
        $productCount   = count($productPaths);

        if ($hasPersons && $productCount > 0) {
            $this->recordStep('Analyzing reference photos & product images with GPT-4o Vision', 'running');
        } elseif ($hasPersons) {
            $this->recordStep('Analyzing reference photos with GPT-4o Vision', 'running');
        } elseif ($productCount > 1) {
            $this->recordStep("Analyzing {$productCount} product images with GPT-4o Vision", 'running');
        } elseif ($productCount === 1) {
            $this->recordStep('Analyzing product image with GPT-4o Vision', 'running');
        }

        $hasProduct = $productCount > 0;

        try {
            $prompt = $promptBuilder->build($this->generation, $config);
        } catch (NonRetryableException $e) {
            $this->failStep();
            $this->markFailed($e->getMessage());
            $this->fail($e);
            return;
        } catch (\Throwable $e) {
            $this->failStep();
            $this->generation->update(['status' => 'pending']);
            throw $e;
        }

        if ($hasPersons || $hasProduct) {
            $this->completeStep();
        }

        $this->recordStep('Building generation prompt', 'done');

        // Generate the image
        $label = 'Generating with ' . strtoupper($model);
        if ($provider->isAsync()) {
            $label = 'Submitting to ' . strtoupper($model);
        }
        $this->recordStep($label, 'running');

        try {
            $result = $provider->generate($this->generation, $prompt, $config);
        } catch (NonRetryableException $e) {
            $this->failStep();
            $this->markFailed($e->getMessage());
            $this->fail($e);
            return;
        } catch (\Throwable $e) {
            $this->failStep();
            $this->generation->update(['status' => 'pending']);
            throw $e; // Let the queue retry
        }

        $this->completeStep();

        // ── Async submit: store prediction ID and wait ────────────────────────
        if ($result->isPending) {
            $this->recordStep('Waiting for AI to finish processing...', 'running');
            $this->mergeMeta(['prediction_id' => $result->predictionId]);
            $this->release(5);
            return;
        }

        // ── Sync complete ─────────────────────────────────────────────────────
        $this->recordStep('Saving result to storage', 'done');

        try {
            $this->markCompleted($result->localUrl);
        } catch (QueryException $e) {
            $this->markFailed('Database error: ' . $e->getMessage());
            $this->fail($e);
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->markFailed($exception->getMessage());
    }

    // ── Step helpers ──────────────────────────────────────────────────────────

    private function recordStep(string $label, string $status = 'running'): void
    {
        try {
            $current         = $this->generation->fresh()->metadata ?? [];
            $steps           = $current['steps'] ?? [];
            $steps[]         = ['label' => $label, 'status' => $status];
            $current['steps'] = $steps;
            $this->generation->update(['metadata' => $current]);
        } catch (\Throwable $e) { \Illuminate\Support\Facades\Log::warning('Generation metadata update failed', ['id' => $this->generation->id, 'error' => $e->getMessage()]); }
    }

    private function completeStep(): void
    {
        try {
            $current = $this->generation->fresh()->metadata ?? [];
            $steps   = $current['steps'] ?? [];
            if (!empty($steps)) {
                $steps[count($steps) - 1]['status'] = 'done';
            }
            $current['steps'] = $steps;
            $this->generation->update(['metadata' => $current]);
        } catch (\Throwable $e) { \Illuminate\Support\Facades\Log::warning('Generation metadata update failed', ['id' => $this->generation->id, 'error' => $e->getMessage()]); }
    }

    private function failStep(): void
    {
        try {
            $current = $this->generation->fresh()->metadata ?? [];
            $steps   = $current['steps'] ?? [];
            if (!empty($steps)) {
                $steps[count($steps) - 1]['status'] = 'failed';
            }
            $current['steps'] = $steps;
            $this->generation->update(['metadata' => $current]);
        } catch (\Throwable $e) { \Illuminate\Support\Facades\Log::warning('Generation metadata update failed', ['id' => $this->generation->id, 'error' => $e->getMessage()]); }
    }

    private function clearSteps(): void
    {
        try {
            $current          = $this->generation->fresh()->metadata ?? [];
            $current['steps'] = [];
            $this->generation->update(['metadata' => $current]);
        } catch (\Throwable $e) { \Illuminate\Support\Facades\Log::warning('Generation metadata update failed', ['id' => $this->generation->id, 'error' => $e->getMessage()]); }
    }

    /** Merge a key into metadata without overwriting other keys (e.g. steps). */
    private function mergeMeta(array $data): void
    {
        try {
            $current = $this->generation->fresh()->metadata ?? [];
            $this->generation->update(['metadata' => array_merge($current, $data)]);
        } catch (\Throwable $e) { \Illuminate\Support\Facades\Log::warning('Generation metadata update failed', ['id' => $this->generation->id, 'error' => $e->getMessage()]); }
    }

    // ── Status helpers ────────────────────────────────────────────────────────

    private function markCompleted(?string $localUrl): void
    {
        $this->generation->update([
            'status'     => 'completed',
            'result_url' => $localUrl,
        ]);
    }

    private function markFailed(string $message): void
    {
        try {
            $this->generation->update([
                'status'        => 'failed',
                'error_message' => substr($message, 0, 500),
            ]);
        } catch (\Throwable $e) { \Illuminate\Support\Facades\Log::warning('Generation metadata update failed', ['id' => $this->generation->id, 'error' => $e->getMessage()]); }
    }
}
