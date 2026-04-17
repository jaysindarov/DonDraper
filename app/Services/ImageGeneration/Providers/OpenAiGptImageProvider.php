<?php

namespace App\Services\ImageGeneration\Providers;

use App\Exceptions\NonRetryableException;
use App\Models\Generation;
use App\Services\ImageGeneration\GenerationResult;
use App\Services\ImageGeneration\ImageStorageService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Files\Image as AiImage;
use Laravel\Ai\Image;

/**
 * Handles gpt-image-1 generations via the Laravel AI SDK (openai driver).
 *
 * When visual references (person or product) are present the SDK routes the
 * request to the images/edits endpoint automatically — any non-empty attachments
 * array triggers it. Without references, images/generations is used instead.
 *
 * The "anchored prompt" prepended when editing ensures the model knows exactly
 * which attached images are the product to reproduce vs. the person to match.
 */
class OpenAiGptImageProvider extends BaseImageProvider
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
        $attachments = $this->buildSdkAttachments($generation);
        $hasAttachments = !empty($attachments);

        $finalPrompt = $hasAttachments
            ? $this->buildAnchoredPrompt($prompt, $attachments, $generation)
            : $prompt;

        try {
            $response = Image::of($finalPrompt)
                ->attachments($attachments)
                ->quality($this->resolveQuality($generation, $modelConfig))
                ->size($this->resolveSize($generation, $modelConfig))
                ->timeout(150)
                ->generate('openai', 'gpt-image-1');
        } catch (RequestException $e) {
            $this->handleRequestException($e, 'gpt-image-1');
        }

        $b64 = $response->firstImage()->image;

        if (empty($b64)) {
            throw new NonRetryableException('gpt-image-1 returned an empty result.');
        }

        return GenerationResult::completed(
            $this->storage->storeFromBase64($generation->id, $b64)
        );
    }

    public function poll(Generation $generation, string $predictionId): GenerationResult
    {
        throw new \LogicException('OpenAiGptImageProvider is synchronous and does not support polling.');
    }

    // ── Attachment building ───────────────────────────────────────────────────

    /**
     * Build the ordered list of SDK attachments for the edits endpoint.
     *
     * Product images come first (multiple angles = higher accuracy), then
     * person reference images. The model weights earlier attachments more heavily,
     * so products are the primary visual anchor.
     *
     * @return AiImage[]
     */
    private function buildSdkAttachments(Generation $generation): array
    {
        $attachments = [];

        // All product angles first
        foreach ($generation->allProductImagePaths() as $path) {
            if (Storage::disk('public')->exists($path)) {
                $attachments[] = AiImage::fromStorage($path, 'public');
            }
        }

        // Person references after products
        foreach ($generation->reference_persons ?? [] as $person) {
            $path = $person['path'] ?? null;
            if ($path && Storage::disk('public')->exists($path)) {
                $attachments[] = AiImage::fromStorage($path, 'public');
            }
        }

        return $attachments;
    }

    // ── Anchored prompt ───────────────────────────────────────────────────────

    /**
     * Prepend explicit role declarations for each attached image group so the
     * model treats product images as exact reproduction targets, not style hints.
     *
     * Without this, gpt-image-1 may treat product images as scenery rather than
     * the primary subject to reproduce with 100% fidelity.
     *
     * @param  AiImage[]  $attachments
     */
    private function buildAnchoredPrompt(
        string $prompt,
        array $attachments,
        Generation $generation,
    ): string {
        $productCount = count($generation->allProductImagePaths());
        $hasPersons   = !empty($generation->reference_persons ?? []);

        // Only count attachments that made it onto disk (some may have been skipped)
        $resolvedProductCount = min(
            $productCount,
            count($attachments) - ($hasPersons ? count($generation->reference_persons ?? []) : 0)
        );

        $anchors = [];

        if ($resolvedProductCount > 0) {
            $type      = $generation->product_type ? " ({$generation->product_type})" : '';
            $imageWord = $resolvedProductCount === 1 ? 'image' : 'images';
            $angleNote = $resolvedProductCount > 1
                ? " The {$resolvedProductCount} product images show it from different angles — use all views to understand its full 3D appearance."
                : '';

            $anchors[] = "PRODUCT REFERENCE: The first {$resolvedProductCount} attached {$imageWord} show{$angleNote} the exact product{$type}. "
                . 'Match its precise shape, colors, logo placement, lens tint, and finish with 100% visual fidelity. '
                . 'CRITICAL: The product must appear WORN BY or naturally HELD BY the model(s) in the scene — '
                . 'NEVER as a separate floating object, cutout, ghost image, or product-shot layer placed on top of the scene. '
                . 'Do not duplicate the product. It exists only as it is used or worn within the composition.';
        }

        if ($hasPersons) {
            $personWord = $resolvedProductCount > 0
                ? 'The remaining reference image(s)'
                : 'The attached reference image(s)';
            $anchors[] = "PERSON REFERENCE: {$personWord} show the person(s) whose exact facial identity must be matched precisely.";
        }

        if (empty($anchors)) {
            return $prompt;
        }

        return implode(' ', $anchors) . ' ' . $prompt;
    }

    // ── Resolution / quality helpers ──────────────────────────────────────────

    /**
     * Return the pixel size string for the API payload.
     * The SDK's OpenAI gateway passes raw values through via its default → $size branch.
     */
    private function resolveSize(Generation $generation, array $modelConfig): string
    {
        $requested = ($generation->attributes ?? [])['resolution'] ?? null;
        $valid     = $modelConfig['sizes'] ?? [];
        $default   = $modelConfig['defaults']['size'] ?? '1024x1024';

        return ($requested && in_array($requested, $valid, strict: true)) ? $requested : $default;
    }

    /**
     * Map UI quality values to gpt-image-1 API values (low / medium / high).
     *
     * UI quality → API quality:
     *   standard  → medium
     *   hd        → high
     *   ultra_hd  → high
     */
    private function resolveQuality(Generation $generation, array $modelConfig): string
    {
        $map = [
            'standard' => 'medium',
            'hd'       => 'high',
            'ultra_hd' => 'high',
        ];

        $uiQuality = ($generation->attributes ?? [])['quality'] ?? null;

        if ($uiQuality && isset($map[$uiQuality])) {
            return $map[$uiQuality];
        }

        return $modelConfig['defaults']['quality'] ?? 'high';
    }
}
