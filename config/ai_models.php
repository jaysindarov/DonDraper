<?php

/**
 * AI Model Configuration
 *
 * Each key is the model identifier stored in generations.model.
 * Changing prompt templates here is all that's needed to tune
 * how each model receives context — no job code changes required.
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
        'driver' => \App\Services\ImageGeneration\Providers\OpenAiGptImageProvider::class,
        'async'  => false,

        'prompts' => [
            // gpt-image-1 receives the ACTUAL product image via the edits endpoint,
            // so the product template just sets the scene — the anchor prompt in the
            // provider already instructs the model to reproduce it with 100% fidelity.
            'person_reference'   => 'The model in this image is {people}. Recreate their exact facial identity and likeness with precision. IMPORTANT: choose a completely different camera angle, pose, and framing than any reference photo — make it feel like an entirely new editorial shoot. {prompt}',
            'product_with_image' => '{prompt}',   // anchor prompt handles product accuracy
            'product_type_only'  => 'Hero product: {type}. Feature it as the centerpiece of a premium marketing campaign. {prompt}',
        ],

        // Generic quality baseline — lighting, mood, camera angle, and art style come
        // AFTER this via attribute directives so user settings always take precedence.
        'style_suffix' => 'Shot by a world-class commercial photographer. Hyper-realistic, ultra-sharp. The image feels aspirational, confident, and crafted for maximum social media impact and virality.',

        'sizes'    => ['1024x1024', '1536x1024', '1024x1536'],
        'defaults' => [
            'size'    => '1024x1024',
            'quality' => 'high',
            'n'       => 1,
        ],
    ],

    'dall-e-3' => [
        'driver' => \App\Services\ImageGeneration\Providers\OpenAiDallEProvider::class,
        'async'  => false,

        'prompts' => [
            'person_reference'   => 'The model is {people}. Match their facial identity precisely. Choose an entirely different camera angle and pose than the reference — make it feel like a brand-new editorial shoot. {prompt}',
            // DALL-E 3 is text-only so product accuracy depends entirely on description quality.
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

    'dall-e-2' => [
        'driver' => \App\Services\ImageGeneration\Providers\OpenAiDallEProvider::class,
        'async'  => false,

        // DALL-E 2 has weaker instruction-following; simpler prompts work better.
        'prompts' => [
            'person_reference'   => 'Person: {people}. Different angle than the reference photo. {prompt}',
            'product_with_image' => '{type} — exact appearance: {description}. {prompt}',
            'product_type_only'  => '{type} as hero product. {prompt}',
        ],

        'style_suffix' => 'Professional photography, perfect lighting, high quality.',

        'sizes'    => ['256x256', '512x512', '1024x1024'],
        'defaults' => [
            'size' => '1024x1024',
            'n'    => 1,
        ],
    ],

    // ── Replicate / Black Forest Labs ─────────────────────────────────────────

    'flux-pro' => [
        'driver'          => \App\Services\ImageGeneration\Providers\ReplicateProvider::class,
        'async'           => true,
        'replicate_model' => 'black-forest-labs/flux-pro',

        'prompts' => [
            'person_reference'   => '{people}, photographed from a fresh editorial angle — not the same as the reference. {prompt}',
            'product_with_image' => 'The hero product is a {type} — reproduce every detail exactly as described: {description}. Feature it prominently. {prompt}',
            'product_type_only'  => '{type} as the hero product. {prompt}',
        ],

        'style_suffix' => 'Commercial photography, perfect lighting, editorial quality, social media viral, ultra-detailed, 8K.',

        'sizes'    => [],   // Flux handles size via width/height params
        'defaults' => [],
    ],

    'flux-dev' => [
        'driver'          => \App\Services\ImageGeneration\Providers\ReplicateProvider::class,
        'async'           => true,
        'replicate_model' => 'black-forest-labs/flux-dev',

        'prompts' => [
            'person_reference'   => '{people}, fresh creative angle — different from the reference. {prompt}',
            'product_with_image' => 'The hero product is a {type} — reproduce every detail exactly as described: {description}. Feature it prominently. {prompt}',
            'product_type_only'  => '{type} as the hero product. {prompt}',
        ],

        'style_suffix' => 'Commercial photography, perfect lighting, editorial quality, ultra-detailed.',

        'sizes'    => [],
        'defaults' => [],
    ],

    'flux-schnell' => [
        'driver'          => \App\Services\ImageGeneration\Providers\ReplicateProvider::class,
        'async'           => true,
        'replicate_model' => 'black-forest-labs/flux-schnell',

        'prompts' => [
            'person_reference'   => '{people}, creative angle. {prompt}',
            'product_with_image' => '{type} — exact appearance: {description}. {prompt}',
            'product_type_only'  => '{type}. {prompt}',
        ],

        'style_suffix' => 'Professional photography, good lighting, high quality.',

        'sizes'    => [],
        'defaults' => [],
    ],

    'stable-diffusion-xl' => [
        'driver'          => \App\Services\ImageGeneration\Providers\ReplicateProvider::class,
        'async'           => true,
        'replicate_model' => 'stability-ai/sdxl:39ed52f2a78e934b3ba6e2a89f5b1c712de7dfea535525255b1aa35c5565e08b',

        'prompts' => [
            'person_reference'   => '{people}, creative fresh angle, {prompt}',
            'product_with_image' => '{type} with exact appearance: {description}, {prompt}',
            'product_type_only'  => '{type}, {prompt}',
        ],

        'style_suffix' => 'commercial photography, perfect lighting, editorial, highly detailed, 8k uhd',

        'sizes'    => [],
        'defaults' => [],
    ],

    'stable-diffusion-3' => [
        'driver'          => \App\Services\ImageGeneration\Providers\ReplicateProvider::class,
        'async'           => true,
        'replicate_model' => 'stability-ai/stable-diffusion-3',

        'prompts' => [
            'person_reference'   => '{people}, shot from a fresh creative angle, {prompt}',
            'product_with_image' => '{type} with exact appearance: {description}, {prompt}',
            'product_type_only'  => '{type}, {prompt}',
        ],

        'style_suffix' => 'commercial photography, perfect lighting, editorial quality, highly detailed',

        'sizes'    => [],
        'defaults' => [],
    ],

];
