<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ProcessImageGeneration implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;
    public int $timeout = 180;
    public int $backoff = 10;

    public function __construct(
        public readonly \App\Models\Generation $generation
    ) {}

    public function middleware(): array
    {
        return [new WithoutOverlapping($this->generation->id)];
    }

    public function handle(): void
    {
        $this->generation->refresh();

        if ($this->generation->status === 'completed') {
            return;
        }

        $this->generation->update(['status' => 'processing']);

        try {
            $localUrl = $this->generate();
        } catch (NonRetryableException $e) {
            $this->markFailed($e->getMessage());
            $this->fail($e);
            return;
        } catch (\Throwable $e) {
            $this->generation->update(['status' => 'pending']);
            throw $e;
        }

        try {
            $this->generation->update([
                'status'     => 'completed',
                'result_url' => $localUrl,
            ]);
        } catch (QueryException $e) {
            $this->markFailed('Database error: ' . $e->getMessage());
            $this->fail($e);
        }
    }

    // ─── Entry point ──────────────────────────────────────────────────────────

    private function generate(): string
    {
        $prompt = $this->buildPrompt();
        $model  = $this->generation->model ?? 'gpt-image-1';

        // When reference person photos are provided, use gpt-image-1 edits endpoint
        // so the model can actually SEE the real faces — far more accurate than text descriptions alone
        $referencePersons = $this->generation->reference_persons ?? [];

        if (!empty($referencePersons) && $this->isGptImageModel($model)) {
            $imageData = $this->generateWithGptImage1Edits($prompt, $referencePersons);
            return $this->storeBase64($imageData);
        }

        if ($this->isGptImageModel($model)) {
            $imageData = $this->generateWithGptImage1($prompt);
            return $this->storeBase64($imageData);
        }

        // DALL-E 3 / DALL-E 2 fallback
        $url = $this->generateWithDallE($prompt);
        return $this->storeFromUrl($url);
    }

    // ─── Prompt building ──────────────────────────────────────────────────────

    private function buildPrompt(): string
    {
        $prompt = $this->generation->prompt;

        // Analyze each reference person with GPT-4o Vision and inject descriptions
        $personDescriptions = [];
        foreach ($this->generation->reference_persons ?? [] as $person) {
            $desc = $this->analyzePersonImage($person['path']);
            if ($desc) {
                $name = $person['name'] ?? 'Person';
                $personDescriptions[] = "{$name}: {$desc}";
            }
        }

        if (!empty($personDescriptions)) {
            $people = implode('. ', $personDescriptions);
            $prompt = "IMPORTANT — The people in this image must closely match these specific references. {$people}. Scene: {$prompt}";
        }

        // Analyze product image if provided
        if ($this->generation->product_image_path) {
            $productDesc = $this->analyzeProductImage($this->generation->product_image_path);
            if ($productDesc) {
                $type = $this->generation->product_type ? "({$this->generation->product_type})" : '';
                $prefix = "The image must prominently feature this exact product {$type}: {$productDesc}.";
                $prompt = "{$prefix} {$prompt}";
            }
        } elseif ($this->generation->product_type) {
            $prompt = "Featuring a {$this->generation->product_type} as the hero product. {$prompt}";
        }

        return $prompt;
    }

    // ─── GPT-4o Vision analysis ───────────────────────────────────────────────

    private function analyzePersonImage(string $path): ?string
    {
        return $this->analyzeImageWithVision(
            $path,
            'Describe this person\'s physical appearance for an AI image generation prompt. '
            . 'Cover: exact skin tone, hair color and style, eye color, facial features, build, age range, and any distinctive features. '
            . 'One concise paragraph. No preamble.'
        );
    }

    private function analyzeProductImage(string $path): ?string
    {
        return $this->analyzeImageWithVision(
            $path,
            'Describe this product for an AI image generation prompt. '
            . 'Cover: exact colors, shape, key visual features, any visible branding or text, and material/finish. '
            . 'One concise sentence. No preamble.'
        );
    }

    private function analyzeImageWithVision(string $storagePath, string $instruction): ?string
    {
        if (!Storage::disk('public')->exists($storagePath)) {
            return null;
        }

        $imageData = Storage::disk('public')->get($storagePath);
        $mimeType  = Storage::disk('public')->mimeType($storagePath) ?: 'image/jpeg';
        $base64    = base64_encode($imageData);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.openai.key'),
            'Content-Type'  => 'application/json',
        ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
            'model'      => 'gpt-4o',
            'max_tokens' => 300,
            'messages'   => [[
                'role'    => 'user',
                'content' => [
                    [
                        'type'      => 'image_url',
                        'image_url' => [
                            'url'    => "data:{$mimeType};base64,{$base64}",
                            'detail' => 'low',
                        ],
                    ],
                    ['type' => 'text', 'text' => $instruction],
                ],
            ]],
        ]);

        if ($response->failed()) {
            return null; // Don't block generation if vision fails
        }

        return trim($response->json('choices.0.message.content') ?? '');
    }

    // ─── gpt-image-1: edits endpoint (reference photos → accurate faces) ──────

    /**
     * Uses the edits endpoint which accepts the actual reference photos as inputs.
     * gpt-image-1 can see the real faces/products and use them as visual reference,
     * resulting in much more accurate likenesses than text descriptions alone.
     */
    private function generateWithGptImage1Edits(string $prompt, array $referencePersons): string
    {
        $multipart = [];

        // Add reference person images — the model sees the actual faces
        foreach ($referencePersons as $person) {
            $path = $person['path'] ?? null;
            if ($path && Storage::disk('public')->exists($path)) {
                $multipart[] = [
                    'name'     => 'image[]',
                    'contents' => Storage::disk('public')->get($path),
                    'filename' => basename($path),
                    'headers'  => ['Content-Type' => Storage::disk('public')->mimeType($path) ?: 'image/jpeg'],
                ];
            }
        }

        // Also add product image if provided
        $productPath = $this->generation->product_image_path;
        if ($productPath && Storage::disk('public')->exists($productPath)) {
            $multipart[] = [
                'name'     => 'image[]',
                'contents' => Storage::disk('public')->get($productPath),
                'filename' => basename($productPath),
                'headers'  => ['Content-Type' => Storage::disk('public')->mimeType($productPath) ?: 'image/jpeg'],
            ];
        }

        if (empty($multipart)) {
            // No valid images found — fall back to text-only generation
            return $this->generateWithGptImage1($prompt);
        }

        $multipart[] = ['name' => 'model',   'contents' => 'gpt-image-1'];
        $multipart[] = ['name' => 'prompt',  'contents' => $prompt];
        $multipart[] = ['name' => 'n',       'contents' => '1'];
        $multipart[] = ['name' => 'size',    'contents' => $this->resolveGptImageSize()];
        $multipart[] = ['name' => 'quality', 'contents' => 'high'];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.openai.key'),
        ])->timeout(150)->asMultipart()->post('https://api.openai.com/v1/images/edits', $multipart);

        return $this->extractGptImageResult($response, 'gpt-image-1 edits');
    }

    // ─── gpt-image-1: text-only generations ───────────────────────────────────

    private function generateWithGptImage1(string $prompt): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.openai.key'),
            'Content-Type'  => 'application/json',
        ])->timeout(150)->post('https://api.openai.com/v1/images/generations', [
            'model'   => 'gpt-image-1',
            'prompt'  => $prompt,
            'n'       => 1,
            'size'    => $this->resolveGptImageSize(),
            'quality' => 'high',
        ]);

        return $this->extractGptImageResult($response, 'gpt-image-1');
    }

    private function extractGptImageResult(\Illuminate\Http\Client\Response $response, string $context): string
    {
        if ($response->status() === 400) {
            throw new NonRetryableException("{$context} rejected request: " . ($response->json('error.message') ?? $response->body()));
        }

        if ($response->status() === 429 || $response->status() >= 500) {
            throw new \RuntimeException("{$context} temporary error ({$response->status()})");
        }

        if ($response->failed()) {
            throw new NonRetryableException("{$context} error: " . $response->body());
        }

        $b64 = $response->json('data.0.b64_json');
        if (empty($b64)) {
            throw new NonRetryableException("{$context} returned empty result");
        }

        return $b64;
    }

    // ─── DALL-E 3 / DALL-E 2 (fallback) ─────────────────────────────────────

    private function generateWithDallE(string $prompt): string
    {
        $attributes = $this->generation->attributes ?? [];
        $model      = $this->generation->model ?? 'dall-e-3';
        $size       = $this->resolveDallESize($model, $attributes['resolution'] ?? '1024x1024');

        $payload = [
            'model'           => $model,
            'prompt'          => $prompt,
            'n'               => 1,
            'size'            => $size,
            'response_format' => 'url',
        ];

        if ($model === 'dall-e-3') {
            $quality = $attributes['quality'] ?? 'hd';
            $payload['quality'] = $quality === 'standard' ? 'standard' : 'hd';
            $payload['style']   = in_array($attributes['art_style'] ?? '', ['vivid', 'natural'])
                ? $attributes['art_style'] : 'vivid';
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.openai.key'),
            'Content-Type'  => 'application/json',
        ])->timeout(90)->post('https://api.openai.com/v1/images/generations', $payload);

        if ($response->status() === 400) {
            throw new NonRetryableException('OpenAI rejected: ' . ($response->json('error.message') ?? $response->body()));
        }

        if ($response->status() === 429 || $response->status() >= 500) {
            throw new \RuntimeException('OpenAI temporary error (' . $response->status() . ')');
        }

        if ($response->failed()) {
            throw new NonRetryableException('DALL-E error: ' . $response->body());
        }

        $url = $response->json('data.0.url');
        if (empty($url)) {
            throw new NonRetryableException('DALL-E returned empty URL');
        }

        return $url;
    }

    // ─── Storage helpers ──────────────────────────────────────────────────────

    private function storeBase64(string $b64): string
    {
        $imageData   = base64_decode($b64);
        $storagePath = "generations/{$this->generation->id}.png";
        Storage::disk('public')->put($storagePath, $imageData);

        return Storage::disk('public')->url($storagePath);
    }

    private function storeFromUrl(string $url): string
    {
        $imageData   = Http::timeout(30)->get($url)->body();
        $storagePath = "generations/{$this->generation->id}.png";
        Storage::disk('public')->put($storagePath, $imageData);

        return Storage::disk('public')->url($storagePath);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function isGptImageModel(string $model): bool
    {
        return $model === 'gpt-image-1';
    }

    private function resolveGptImageSize(): string
    {
        $attributes = $this->generation->attributes ?? [];
        $resolution = $attributes['resolution'] ?? '1024x1024';

        // gpt-image-1 supported sizes
        $valid = ['1024x1024', '1536x1024', '1024x1536'];
        return in_array($resolution, $valid) ? $resolution : '1024x1024';
    }

    private function resolveDallESize(string $model, string $requested): string
    {
        if ($model === 'dall-e-3') {
            $valid = ['1024x1024', '1792x1024', '1024x1792'];
            return in_array($requested, $valid) ? $requested : '1024x1024';
        }
        if ($model === 'dall-e-2') {
            $valid = ['256x256', '512x512', '1024x1024'];
            return in_array($requested, $valid) ? $requested : '1024x1024';
        }
        return '1024x1024';
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
        $this->markFailed($exception->getMessage());
    }
}

class NonRetryableException extends \RuntimeException {}
