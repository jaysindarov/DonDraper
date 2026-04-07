<?php

/**
 * AI Model Configuration
 *
 * Each key is the model identifier stored in generations.model.
 * Changing prompt templates here is all that's needed to tune
 * how each model receives context — no job code changes required.
 *
 * To add a new model:
 *   1. Create a provider class implementing ImageGenerationProvider.
 *   2. Add an entry here with 'driver' pointing to that class.
 *   3. No other code changes required.
 *
 * Template placeholders available in prompt strings:
 *   {prompt}       — the user's base prompt
 *   {people}       — comma-joined vision descriptions of reference persons
 *   {type}         — product type label (e.g. "sneaker", "watch")
 *   {description}  — vision description of the product image
 */

return [

    // ── OpenAI ────────────────────────────────────────────────────────────────

    'gpt-image-1' => [
        'label'       => 'GPT Image 1',
        'provider'    => 'OpenAI',
        'description' => 'Best quality. Accepts real reference photos for accurate likenesses.',
        'recommended' => true,
        'driver' => \App\Services\ImageGeneration\Providers\OpenAiGptImageProvider::class,
        'async'  => false,

        'prompts' => [
            // gpt-image-1 receives the ACTUAL product image via the edits endpoint,
            // so the product template just sets the scene — the anchor prompt in the
            // provider already instructs the model to reproduce it with 100% fidelity.
            'person_reference'   => 'The model in this image is {people}. Recreate their exact facial identity and likeness with precision. IMPORTANT: choose a completely different camera angle, pose, and framing than any reference photo — make it feel like an entirely new editorial shoot. {prompt}',
            'product_with_image' => '{prompt}',
            'product_type_only'  => 'Hero product: {type}. Feature it as the centerpiece of a premium marketing campaign. {prompt}',
        ],

        'style_suffix' => 'Shot by a world-class commercial photographer. Hyper-realistic, ultra-sharp. The image feels aspirational, confident, and crafted for maximum social media impact and virality.',

        'sizes'    => ['1024x1024', '1536x1024', '1024x1536'],
        'defaults' => [
            'size'    => '1024x1024',
            'quality' => 'high',
            'n'       => 1,
        ],
    ],

    'dall-e-3' => [
        'label'       => 'DALL·E 3',
        'provider'    => 'OpenAI',
        'description' => 'High quality, vivid styles. Text-only (no reference photos).',
        'recommended' => false,
        'driver' => \App\Services\ImageGeneration\Providers\OpenAiDallEProvider::class,
        'async'  => false,

        'prompts' => [
            'person_reference'   => 'The model is {people}. Match their facial identity precisely. Choose an entirely different camera angle and pose than the reference — make it feel like a brand-new editorial shoot. {prompt}',
            'product_with_image' => 'The hero product is a {type} with the following exact appearance — reproduce every detail faithfully: {description}. Do not alter the product colors, logo, shape, or finish. Feature it prominently as the centerpiece. {prompt}',
            'product_type_only'  => 'Featuring {type} as the hero product of a premium campaign. {prompt}',
        ],

        'style_suffix' => 'World-class commercial photography. Ultra-realistic. Crafted for editorial and social media virality.',

        'sizes'    => ['1024x1024', '1792x1024', '1024x1792'],
        'defaults' => [
            'size'    => '1024x1024',
            'quality' => 'hd',
            'style'   => 'vivid',
            'n'       => 1,
        ],
    ],

    // ── Google ────────────────────────────────────────────────────────────────
    // Requires GOOGLE_AI_API_KEY in .env
    // google_model maps directly to the Google Imagen API model slug.

    'google-nano-banana' => [
        'label'       => 'Nano Banana',
        'provider'    => 'Google',
        'description' => 'Google Imagen 3. Balanced quality and speed.',
        'recommended' => false,
        'driver'       => \App\Services\ImageGeneration\Providers\GoogleImageProvider::class,
        'async'        => false,
        'google_model' => 'imagen-3.0-generate-002',

        'prompts' => [
            'person_reference'   => '{people}, editorial style, fresh camera angle different from the reference. {prompt}',
            'product_with_image' => 'The hero product is a {type} — reproduce every detail exactly as described: {description}. Feature it prominently. {prompt}',
            'product_type_only'  => '{type} as the hero product of a premium campaign. {prompt}',
        ],

        'style_suffix' => 'World-class commercial photography, perfect lighting, ultra-detailed, aspirational.',

        'defaults' => [
            'aspect_ratio' => '1:1',
        ],
    ],

    'google-nano-banana-pro' => [
        'label'       => 'Nano Banana Pro',
        'provider'    => 'Google',
        'description' => 'Google Imagen 3 Fast. Premium sharpness for brand campaigns.',
        'recommended' => false,
        'driver'       => \App\Services\ImageGeneration\Providers\GoogleImageProvider::class,
        'async'        => false,
        'google_model' => 'imagen-3.0-fast-generate-001',

        'prompts' => [
            'person_reference'   => '{people}, high-fidelity editorial portrait, fresh angle from reference. {prompt}',
            'product_with_image' => 'The hero product is a {type} — reproduce every detail with pixel-perfect fidelity: {description}. Feature it as the scene centerpiece. {prompt}',
            'product_type_only'  => '{type} hero product, premium luxury campaign aesthetic. {prompt}',
        ],

        'style_suffix' => 'Hyper-realistic commercial photography, 8K, ultra-sharp, crafted for premium brand campaigns.',

        'defaults' => [
            'aspect_ratio' => '1:1',
        ],
    ],

    // ── xAI Grok ──────────────────────────────────────────────────────────────
    // Requires GROK_API_KEY in .env
    // Docs: https://docs.x.ai/api/image-generation

    'grok' => [
        'label'       => 'Grok',
        'provider'    => 'xAI',
        'description' => 'xAI Grok. Creative and experimental style.',
        'recommended' => false,
        'driver'     => \App\Services\ImageGeneration\Providers\GrokImageProvider::class,
        'async'      => false,
        'grok_model' => 'grok-2-image-1212',

        'prompts' => [
            'person_reference'   => '{people}, captured from a bold new angle — not the same as the reference photo. {prompt}',
            'product_with_image' => 'The hero product is a {type} — reproduce every detail exactly as described: {description}. Feature it prominently in the scene. {prompt}',
            'product_type_only'  => '{type} as the hero product. {prompt}',
        ],

        'style_suffix' => 'Commercial photography, perfect lighting, editorial quality, ultra-detailed.',

        'defaults' => [],
    ],

];
