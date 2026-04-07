<?php

namespace App\Services\ImageGeneration;

use App\Models\Generation;

/**
 * Builds the final prompt for a generation by layering:
 *   1. Person identity references (face descriptions from vision analysis)
 *   2. Product context (description from vision analysis or type label)
 *   3. Attribute directives (art style, lighting, mood, camera angle, color palette, detail level)
 *   4. Model-specific style suffix
 *
 * Attribute directives are the critical layer that was previously missing.
 * Every setting the user configures in the UI must end up in the prompt —
 * OpenAI models have no API parameter for lighting or mood; it is all natural language.
 */
class PromptBuilder
{
    /**
     * Maps each select-type attribute key → option value → prompt phrase.
     * Phrases are written to work naturally when appended to any generation prompt.
     */
    private const ATTRIBUTE_PHRASES = [
        'art_style' => [
            'photorealistic' => 'ultra-photorealistic photography, shot on high-end camera',
            'oil_painting'   => 'oil painting with visible expressive brushwork',
            'watercolor'     => 'soft loose watercolor painting on textured paper',
            'digital_art'    => 'polished professional digital art illustration',
            'anime'          => 'anime/manga illustration style',
            'sketch'         => 'detailed pencil sketch with crosshatching',
            'comic'          => 'bold comic book style with strong ink outlines',
            'cinematic'      => 'cinematic photography, anamorphic lens, film still',
            'minimalist'     => 'clean minimalist design, ample negative space',
            'abstract'       => 'expressive abstract art',
            'impressionist'  => 'impressionist painting with loose expressive brushstrokes',
            'cyberpunk'      => 'cyberpunk aesthetic — neon, chrome, rain-slicked streets',
            'fantasy'        => 'epic high-fantasy concept art',
            'neon'           => 'neon noir with glowing colored lights and deep shadows',
        ],
        'lighting' => [
            'natural'     => 'soft natural daylight',
            'golden_hour' => 'golden hour warm sunlight, magic hour, long shadows',
            'studio'      => 'professional three-point studio lighting, clean highlights',
            'dramatic'    => 'dramatic high-contrast chiaroscuro, deep blacks',
            'soft'        => 'soft wrap-around diffused light, minimal harsh shadows',
            'neon'        => 'vivid neon glow lighting with colored light spill',
            'backlit'     => 'strong rim backlight, silhouette, lens flare',
            'volumetric'  => 'volumetric god rays, atmospheric haze and depth',
        ],
        'color_palette' => [
            'vibrant'         => 'vibrant fully saturated colors, rich and bold',
            'muted'           => 'muted desaturated pastel tones, soft and faded',
            'monochrome'      => 'monochromatic single-hue color scheme',
            'warm'            => 'warm amber golden and terracotta tones',
            'cool'            => 'cool blue teal and silver tones',
            'neon'            => 'electric neon palette — hot pink, cyan, lime',
            'earthy'          => 'earthy natural palette — terracotta, olive, sand, brown',
            'black_and_white' => 'strict black and white, no color whatsoever',
        ],
        'mood' => [
            'joyful'      => 'joyful bright uplifting sunny energy',
            'melancholic' => 'melancholic contemplative introspective mood',
            'mysterious'  => 'mysterious enigmatic eerie atmosphere',
            'epic'        => 'epic powerful heroic grand atmosphere',
            'serene'      => 'calm serene peaceful tranquil atmosphere',
            'dramatic'    => 'intense high-drama emotional tension',
            'dreamy'      => 'dreamy surreal ethereal soft-focus atmosphere',
            'dark'        => 'dark brooding unsettling moody atmosphere',
            'whimsical'   => 'playful whimsical fairy-tale magical feel',
        ],
        'camera_angle' => [
            'eye_level'   => 'natural eye-level perspective',
            'birds_eye'   => "dramatic bird's eye view, shot from directly overhead",
            'worms_eye'   => "powerful worm's eye view, camera looking sharply upward",
            'dutch_angle' => 'Dutch angle — tilted camera for tension and unease',
            'close_up'    => 'extreme close-up, tight framing on the subject',
            'wide_shot'   => 'wide establishing shot, full environment visible',
            'macro'       => 'macro photography, extreme close-up capturing fine textures',
        ],
    ];

    public function __construct(
        private readonly VisionAnalyzer $vision,
    ) {}

    /**
     * @param  array<string, mixed>  $modelConfig  Entry from config/ai_models.php
     */
    public function build(Generation $generation, array $modelConfig): string
    {
        $prompt    = $generation->prompt;
        $templates = $modelConfig['prompts'] ?? [];

        $prompt = $this->applyPersonReferences($generation, $prompt, $templates);
        $prompt = $this->applyProductContext($generation, $prompt, $templates);
        $prompt = $this->applyStyleSuffix($prompt, $modelConfig);
        // Attribute directives go LAST so user-specific settings override the generic style suffix
        $prompt = $this->applyAttributeDirectives($generation, $prompt);

        return $prompt;
    }

    // ── Attribute directives ──────────────────────────────────────────────────

    /**
     * Translate the user's UI settings (art_style, lighting, mood, etc.) into
     * an explicit REQUIRED VISUAL SETTINGS block appended after the prompt.
     *
     * Using a labeled block (not a comma-separated suffix) ensures gpt-image-1
     * treats these as mandatory instructions rather than optional style hints —
     * especially important when a strong product reference image is also present.
     */
    private function applyAttributeDirectives(Generation $generation, string $prompt): string
    {
        $attrs      = $generation->attributes ?? [];
        $directives = [];

        // Select-type attributes — map each to a labeled directive line
        $labelMap = [
            'art_style'     => 'Art style',
            'lighting'      => 'Lighting',
            'mood'          => 'Mood',
            'camera_angle'  => 'Camera angle',
            'color_palette' => 'Color palette',
        ];

        foreach ($labelMap as $key => $label) {
            $value = $attrs[$key] ?? null;
            if ($value && isset(self::ATTRIBUTE_PHRASES[$key][$value])) {
                $directives[] = "{$label}: " . self::ATTRIBUTE_PHRASES[$key][$value];
            }
        }

        // Range attributes — only inject when non-neutral
        $detailLevel = isset($attrs['detail_level']) ? (int) $attrs['detail_level'] : null;
        if ($detailLevel !== null) {
            $phrase = match (true) {
                $detailLevel >= 9 => 'ultra-high detail — every element meticulously rendered',
                $detailLevel >= 7 => 'highly detailed',
                $detailLevel <= 3 => 'simplified low-detail style',
                default           => null,
            };
            if ($phrase) {
                $directives[] = "Detail level: {$phrase}";
            }
        }

        $sharpness = isset($attrs['sharpness']) ? (int) $attrs['sharpness'] : null;
        if ($sharpness !== null) {
            $phrase = match (true) {
                $sharpness >= 9 => 'razor-sharp focus, tack-sharp across the entire frame',
                $sharpness >= 7 => 'sharp crisp focus',
                $sharpness <= 3 => 'intentional soft focus, shallow depth of field, gentle blur',
                default         => null,
            };
            if ($phrase) {
                $directives[] = "Sharpness: {$phrase}";
            }
        }

        if (empty($directives)) {
            return $prompt;
        }

        return rtrim($prompt, '. ')
            . "\n\nREQUIRED VISUAL SETTINGS — apply all of these exactly:\n"
            . implode("\n", array_map(fn ($d) => "- {$d}", $directives));
    }

    // ── Style suffix ──────────────────────────────────────────────────────────

    private function applyStyleSuffix(string $prompt, array $modelConfig): string
    {
        $suffix = $modelConfig['style_suffix'] ?? null;

        if (empty($suffix)) {
            return $prompt;
        }

        return rtrim($prompt, '. ') . '. ' . $suffix;
    }

    // ── Person references ─────────────────────────────────────────────────────

    private function applyPersonReferences(Generation $generation, string $prompt, array $templates): string
    {
        $persons = $generation->reference_persons ?? [];

        if (empty($persons) || empty($templates['person_reference'])) {
            return $prompt;
        }

        $descriptions = [];
        foreach ($persons as $person) {
            $desc = $this->vision->describePerson($person['path'] ?? '');
            if ($desc) {
                $name           = $person['name'] ?? 'Person';
                $descriptions[] = "{$name}: {$desc}";
            }
        }

        if (empty($descriptions)) {
            return $prompt;
        }

        return $this->interpolate($templates['person_reference'], [
            'people' => implode('. ', $descriptions),
            'prompt' => $prompt,
        ]);
    }

    // ── Product context ───────────────────────────────────────────────────────

    private function applyProductContext(Generation $generation, string $prompt, array $templates): string
    {
        $paths = $generation->allProductImagePaths();

        if (!empty($paths) && !empty($templates['product_with_image'])) {
            $descriptions = $this->describeAllProductImages($paths);

            if (!empty($descriptions)) {
                $type = $generation->product_type ? "({$generation->product_type})" : '';

                $combined = count($descriptions) === 1
                    ? $descriptions[0]
                    : 'Multiple angles of the same product — ' . implode('. ', $descriptions);

                return $this->interpolate($templates['product_with_image'], [
                    'type'        => $type,
                    'description' => $combined,
                    'prompt'      => $prompt,
                ]);
            }
        }

        if ($generation->product_type && !empty($templates['product_type_only'])) {
            return $this->interpolate($templates['product_type_only'], [
                'type'   => $generation->product_type,
                'prompt' => $prompt,
            ]);
        }

        return $prompt;
    }

    /**
     * @param  string[]  $paths
     * @return string[]
     */
    private function describeAllProductImages(array $paths): array
    {
        $angleLabels  = ['front view', 'side view', 'back view', 'detail view'];
        $descriptions = [];

        foreach ($paths as $i => $path) {
            $desc = $this->vision->describeProduct($path);
            if ($desc) {
                $label          = count($paths) > 1 ? ($angleLabels[$i] ?? "angle " . ($i + 1)) : null;
                $descriptions[] = $label ? "{$label}: {$desc}" : $desc;
            }
        }

        return $descriptions;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** @param array<string, string> $vars */
    private function interpolate(string $template, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }

        return $template;
    }
}
