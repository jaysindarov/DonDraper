<?php

namespace App\Services\ImageGeneration\Providers;

use App\Contracts\ImageGenerationProvider;
use App\Exceptions\NonRetryableException;
use App\Models\Generation;
use App\Services\ImageGeneration\GenerationResult;
use App\Services\ImageGeneration\ImageStorageService;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Handles gpt-image-1 generations.
 *
 * When reference person photos are present the edits endpoint is used so the
 * model can see the actual faces — far more accurate than text descriptions.
 * Without reference photos the standard generations endpoint is used.
 */
class OpenAiGptImageProvider implements ImageGenerationProvider
{
    public function __construct(
        private readonly ImageStorageService $storage,
    ) {}

    public function isAsync(): bool
    {
        return false;
    }

    public function generate(Generation $generation, string $prompt, array $modelConfig): GenerationResult
    {
        $hasPersons  = !empty($generation->reference_persons ?? []);
        $hasProduct  = !empty($generation->allProductImagePaths());

        // Use the edits endpoint whenever ANY visual reference exists (persons OR product).
        // The edits endpoint physically sends the image bytes to the model so it can SEE
        // the actual product/face — not guess from a text description.
        // Without this, product-only generations fall back to text-only and lose ~30% accuracy.
        $b64 = ($hasPersons || $hasProduct)
            ? $this->generateViaEditsEndpoint($generation, $prompt, $modelConfig)
            : $this->generateViaGenerationsEndpoint($generation, $prompt, $modelConfig);

        $localUrl = $this->storage->storeFromBase64($generation->id, $b64);

        return GenerationResult::completed($localUrl);
    }

    public function poll(Generation $generation, string $predictionId): GenerationResult
    {
        throw new \LogicException('OpenAiGptImageProvider is synchronous and does not support polling.');
    }

    // ── Edits endpoint (accepts real reference images for accurate likenesses) ─

    private function generateViaEditsEndpoint(Generation $generation, string $prompt, array $modelConfig): string
    {
        ['parts' => $parts, 'productCount' => $productCount, 'hasPersons' => $hasPersons]
            = $this->buildReferenceImageParts($generation);

        if (empty($parts)) {
            // No valid images on disk — fall back to text-only
            return $this->generateViaGenerationsEndpoint($generation, $prompt, $modelConfig);
        }

        // Prepend an explicit anchor so the model knows what role each attached image plays.
        // Without this, gpt-image-1 may treat product images as scenery rather than
        // the primary subject to reproduce with exact accuracy.
        $anchoredPrompt = $this->buildAnchoredPrompt($prompt, $productCount, $hasPersons, $generation);

        $parts[] = ['name' => 'model',   'contents' => 'gpt-image-1'];
        $parts[] = ['name' => 'prompt',  'contents' => $anchoredPrompt];
        $parts[] = ['name' => 'n',       'contents' => '1'];
        $parts[] = ['name' => 'size',    'contents' => $this->resolveSize($generation, $modelConfig)];
        $parts[] = ['name' => 'quality', 'contents' => $this->resolveQuality($generation, $modelConfig)];

        $response = Http::withHeaders(['Authorization' => 'Bearer ' . config('services.openai.key')])
            ->timeout(150)
            ->asMultipart()
            ->post(config('services.openai.base_url') . '/images/edits', $parts);

        return $this->extractB64($response, 'gpt-image-1 edits');
    }

    /**
     * Prepend an explicit role declaration for each attached image group so the
     * model treats product images as exact reproduction targets, not style hints.
     */
    private function buildAnchoredPrompt(
        string $prompt,
        int $productCount,
        bool $hasPersons,
        Generation $generation,
    ): string {
        $anchors = [];

        if ($productCount > 0) {
            $type      = $generation->product_type ? " ({$generation->product_type})" : '';
            $imageWord = $productCount === 1 ? 'image' : 'images';
            $angleNote = $productCount > 1
                ? " The {$productCount} product images show it from different angles — use all views to understand its full 3D appearance."
                : '';

            $anchors[] = "PRODUCT REFERENCE: The first {$productCount} attached {$imageWord} show{$angleNote} the exact product{$type}. "
                . 'Match its precise shape, colors, logo placement, lens tint, and finish with 100% visual fidelity. '
                . 'CRITICAL: The product must appear WORN BY or naturally HELD BY the model(s) in the scene — '
                . 'NEVER as a separate floating object, cutout, ghost image, or product-shot layer placed on top of the scene. '
                . 'Do not duplicate the product. It exists only as it is used or worn within the composition.';
        }

        if ($hasPersons) {
            $personWord = $productCount > 0 ? 'The remaining reference image(s)' : 'The attached reference image(s)';
            $anchors[] = "PERSON REFERENCE: {$personWord} show the person(s) whose exact facial identity must be matched precisely.";
        }

        if (empty($anchors)) {
            return $prompt;
        }

        return implode(' ', $anchors) . ' ' . $prompt;
    }

    /**
     * Build the multipart image parts for the edits endpoint.
     *
     * All product images come first (multiple angles = more accuracy), then
     * person reference images. Ordering matters: the model treats earlier
     * images as more dominant visual references.
     *
     * @return array{parts: array, productCount: int, hasPersons: bool}
     */
    private function buildReferenceImageParts(Generation $generation): array
    {
        $parts        = [];
        $productCount = 0;
        $hasPersons   = false;

        // All product images first (front, side, back, detail — whatever was uploaded)
        foreach ($generation->allProductImagePaths() as $path) {
            if (Storage::disk('public')->exists($path)) {
                $parts[] = [
                    'name'     => 'image[]',
                    'contents' => Storage::disk('public')->get($path),
                    'filename' => basename($path),
                    'headers'  => ['Content-Type' => Storage::disk('public')->mimeType($path) ?: 'image/jpeg'],
                ];
                $productCount++;
            }
        }

        // Person reference images after the product
        foreach ($generation->reference_persons ?? [] as $person) {
            $path = $person['path'] ?? null;
            if ($path && Storage::disk('public')->exists($path)) {
                $parts[] = [
                    'name'     => 'image[]',
                    'contents' => Storage::disk('public')->get($path),
                    'filename' => basename($path),
                    'headers'  => ['Content-Type' => Storage::disk('public')->mimeType($path) ?: 'image/jpeg'],
                ];
                $hasPersons = true;
            }
        }

        return ['parts' => $parts, 'productCount' => $productCount, 'hasPersons' => $hasPersons];
    }

    // ── Generations endpoint (text-only) ──────────────────────────────────────

    private function generateViaGenerationsEndpoint(Generation $generation, string $prompt, array $modelConfig): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.openai.key'),
            'Content-Type'  => 'application/json',
        ])->timeout(150)->post(config('services.openai.base_url') . '/images/generations', [
            'model'   => 'gpt-image-1',
            'prompt'  => $prompt,
            'n'       => 1,
            'size'    => $this->resolveSize($generation, $modelConfig),
            'quality' => $this->resolveQuality($generation, $modelConfig),
        ]);

        return $this->extractB64($response, 'gpt-image-1');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function resolveSize(Generation $generation, array $modelConfig): string
    {
        $requested = ($generation->attributes ?? [])['resolution'] ?? null;
        $valid      = $modelConfig['sizes'] ?? [];
        $default    = $modelConfig['defaults']['size'] ?? '1024x1024';

        return ($requested && in_array($requested, $valid)) ? $requested : $default;
    }

    /**
     * Map UI quality values to gpt-image-1 API values (low / medium / high).
     * UI: standard → medium, hd → high, ultra_hd → high.
     */
    private function resolveQuality(Generation $generation, array $modelConfig): string
    {
        $uiQuality = ($generation->attributes ?? [])['quality'] ?? null;

        $map = [
            'standard'  => 'medium',
            'hd'        => 'high',
            'ultra_hd'  => 'high',
        ];

        if ($uiQuality && isset($map[$uiQuality])) {
            return $map[$uiQuality];
        }

        return $modelConfig['defaults']['quality'] ?? 'high';
    }

    private function extractB64(Response $response, string $context): string
    {
        if ($response->status() === 400) {
            throw new NonRetryableException(
                "{$context} rejected request: " . ($response->json('error.message') ?? $response->body())
            );
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
}
