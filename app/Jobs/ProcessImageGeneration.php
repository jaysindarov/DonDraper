<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessImageGeneration implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        public readonly \App\Models\Generation $generation
    ) {}

    public function handle(): void
    {
        $this->generation->update(['status' => 'processing']);

        try {
            // Using Laravel AI SDK — swap provider/model as needed
            $imageUrl = $this->generateWithOpenAI();

            $this->generation->update([
                'status' => 'completed',
                'result_url' => $imageUrl,
            ]);
        } catch (\Throwable $e) {
            $this->generation->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    private function generateWithOpenAI(): string
    {
        $attributes = $this->generation->attributes ?? [];
        $size = $attributes['resolution'] ?? '1024x1024';

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.openai.key'),
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/images/generations', [
            'model' => $this->generation->model ?? 'dall-e-3',
            'prompt' => $this->generation->prompt,
            'n' => 1,
            'size' => $size,
            'quality' => $attributes['quality'] ?? 'hd',
            'style' => in_array($attributes['art_style'] ?? '', ['vivid', 'natural']) ? ($attributes['art_style'] ?? 'vivid') : 'vivid',
            'response_format' => 'url',
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('OpenAI API error: ' . $response->body());
        }

        return $response->json('data.0.url');
    }

    public function failed(\Throwable $exception): void
    {
        $this->generation->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
        ]);
    }
}
