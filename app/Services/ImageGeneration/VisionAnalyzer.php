<?php

namespace App\Services\ImageGeneration;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Uses GPT-4o Vision to produce text descriptions of images
 * so they can be injected into downstream generation prompts.
 *
 * Failures are treated as soft: returns null rather than throwing,
 * so vision analysis never blocks the main generation.
 */
class VisionAnalyzer
{
    private const MODEL = 'gpt-4o';

    public function describePerson(string $storagePath): ?string
    {
        return $this->analyze(
            storagePath: $storagePath,
            instruction: 'Describe this person\'s facial identity and physical characteristics for an AI image generation reference. '
                . 'Focus exclusively on: exact skin tone and undertone, face shape (oval/square/round/heart), eye color and eye shape, '
                . 'nose shape and size, lip shape and fullness, eyebrow thickness and arch, hair color, hair texture (straight/wavy/curly), '
                . 'jaw and cheekbone structure, ethnicity, apparent age range, and any distinctive facial features (dimples, freckles, scars, etc.). '
                . 'DO NOT describe pose, clothing, expression, background, or body below the neck. '
                . 'Write as a precise physical identity profile in one paragraph. No preamble.',
            detail: 'low',    // Faces: low detail is sufficient for identity features
            maxTokens: 300,
        );
    }

    public function describeProduct(string $storagePath): ?string
    {
        return $this->analyze(
            storagePath: $storagePath,
            instruction: 'You are a product photography analyst. Describe this product in precise detail so an AI image generator can reproduce it with 100% visual accuracy. '
                . 'Cover ALL of the following without omitting any: '
                . '(1) BRAND & TEXT: exact brand name, logo style, any text visible on the product and its exact placement/font weight; '
                . '(2) COLORS: exact color names for every part — use precise terms like "matte desert sand beige", "glossy jet black", "brushed rose gold metal"; '
                . '(3) SHAPE & FORM: overall silhouette, proportions, any curves, edges, symmetry or asymmetry; '
                . '(4) MATERIALS & FINISH: every surface material and finish (e.g. "soft-touch matte rubber grip", "polished chrome bezel", "frosted glass lens"); '
                . '(5) KEY DESIGN DETAILS: any distinctive visual elements — stitching, cutouts, embossing, gradients, patterns, hardware; '
                . '(6) PRODUCT CATEGORY: what type of product it is. '
                . 'Write 3–5 descriptive sentences. Be specific and literal — avoid vague words like "stylish" or "modern". No preamble.',
            detail: 'high',   // Products: high detail to capture logos, text, fine finishes
            maxTokens: 600,
        );
    }

    private function analyze(string $storagePath, string $instruction, string $detail, int $maxTokens): ?string
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
        ])->timeout(45)->post(config('services.openai.base_url') . '/chat/completions', [
            'model'      => self::MODEL,
            'max_tokens' => $maxTokens,
            'messages'   => [[
                'role'    => 'user',
                'content' => [
                    [
                        'type'      => 'image_url',
                        'image_url' => [
                            'url'    => "data:{$mimeType};base64,{$base64}",
                            'detail' => $detail,
                        ],
                    ],
                    ['type' => 'text', 'text' => $instruction],
                ],
            ]],
        ]);

        if ($response->failed()) {
            return null;
        }

        return trim($response->json('choices.0.message.content') ?? '') ?: null;
    }
}
