<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GenerationAttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attributes = [
            // Basic
            ['key' => 'aspect_ratio', 'label' => 'Aspect Ratio', 'type' => 'select', 'category' => 'basic', 'applicable_to' => 'both', 'sort_order' => 1,
                'options' => ['1:1' => 'Square (1:1)', '16:9' => 'Landscape (16:9)', '9:16' => 'Portrait (9:16)', '4:3' => 'Standard (4:3)', '3:2' => 'Classic (3:2)', '21:9' => 'Ultrawide (21:9)'],
                'default_value' => '1:1'],
            ['key' => 'resolution', 'label' => 'Resolution', 'type' => 'select', 'category' => 'basic', 'applicable_to' => 'image', 'sort_order' => 2,
                'options' => ['256x256' => '256×256', '512x512' => '512×512', '768x768' => '768×768', '1024x1024' => '1024×1024', '1792x1024' => '1792×1024', '1024x1792' => '1024×1792'],
                'default_value' => '1024x1024'],

            // Style
            ['key' => 'art_style', 'label' => 'Art Style', 'type' => 'select', 'category' => 'style', 'applicable_to' => 'image', 'sort_order' => 10,
                'options' => ['photorealistic' => 'Photorealistic', 'oil_painting' => 'Oil Painting', 'watercolor' => 'Watercolor', 'digital_art' => 'Digital Art', 'anime' => 'Anime', 'sketch' => 'Sketch', 'comic' => 'Comic Book', 'cinematic' => 'Cinematic', 'minimalist' => 'Minimalist', 'abstract' => 'Abstract', 'impressionist' => 'Impressionist', 'cyberpunk' => 'Cyberpunk', 'fantasy' => 'Fantasy', 'neon' => 'Neon Noir'],
                'default_value' => 'photorealistic'],
            ['key' => 'lighting', 'label' => 'Lighting', 'type' => 'select', 'category' => 'style', 'applicable_to' => 'image', 'sort_order' => 11,
                'options' => ['natural' => 'Natural Light', 'golden_hour' => 'Golden Hour', 'studio' => 'Studio Light', 'dramatic' => 'Dramatic', 'soft' => 'Soft Diffused', 'neon' => 'Neon Glow', 'backlit' => 'Backlit', 'volumetric' => 'Volumetric'],
                'default_value' => 'natural'],
            ['key' => 'color_palette', 'label' => 'Color Palette', 'type' => 'select', 'category' => 'style', 'applicable_to' => 'image', 'sort_order' => 12,
                'options' => ['vibrant' => 'Vibrant', 'muted' => 'Muted & Pastel', 'monochrome' => 'Monochrome', 'warm' => 'Warm Tones', 'cool' => 'Cool Tones', 'neon' => 'Neon Colors', 'earthy' => 'Earthy', 'black_and_white' => 'Black & White'],
                'default_value' => 'vibrant'],
            ['key' => 'mood', 'label' => 'Mood & Atmosphere', 'type' => 'select', 'category' => 'style', 'applicable_to' => 'image', 'sort_order' => 13,
                'options' => ['joyful' => 'Joyful', 'melancholic' => 'Melancholic', 'mysterious' => 'Mysterious', 'epic' => 'Epic', 'serene' => 'Serene', 'dramatic' => 'Dramatic', 'dreamy' => 'Dreamy', 'dark' => 'Dark & Moody', 'whimsical' => 'Whimsical'],
                'default_value' => 'serene'],
            ['key' => 'camera_angle', 'label' => 'Camera Angle', 'type' => 'select', 'category' => 'style', 'applicable_to' => 'image', 'sort_order' => 14,
                'options' => ['eye_level' => 'Eye Level', 'birds_eye' => "Bird's Eye", 'worms_eye' => "Worm's Eye", 'dutch_angle' => 'Dutch Angle', 'close_up' => 'Close Up', 'wide_shot' => 'Wide Shot', 'macro' => 'Macro'],
                'default_value' => 'eye_level'],

            // Quality
            ['key' => 'quality', 'label' => 'Quality', 'type' => 'select', 'category' => 'quality', 'applicable_to' => 'image', 'sort_order' => 20,
                'options' => ['standard' => 'Standard', 'hd' => 'HD', 'ultra_hd' => 'Ultra HD'],
                'default_value' => 'hd'],
            ['key' => 'detail_level', 'label' => 'Detail Level', 'type' => 'range', 'category' => 'quality', 'applicable_to' => 'image', 'sort_order' => 21,
                'default_value' => '7', 'min' => '1', 'max' => '10', 'step' => '1'],
            ['key' => 'sharpness', 'label' => 'Sharpness', 'type' => 'range', 'category' => 'quality', 'applicable_to' => 'image', 'sort_order' => 22,
                'default_value' => '5', 'min' => '1', 'max' => '10', 'step' => '1'],

            // Advanced
            ['key' => 'steps', 'label' => 'Diffusion Steps', 'type' => 'range', 'category' => 'advanced', 'applicable_to' => 'image', 'sort_order' => 30,
                'default_value' => '30', 'min' => '10', 'max' => '150', 'step' => '5',
                'description' => 'More steps = more detail but slower generation'],
            ['key' => 'guidance_scale', 'label' => 'Guidance Scale (CFG)', 'type' => 'range', 'category' => 'advanced', 'applicable_to' => 'image', 'sort_order' => 31,
                'default_value' => '7', 'min' => '1', 'max' => '20', 'step' => '0.5',
                'description' => 'How closely to follow the prompt. Higher = more literal'],
            ['key' => 'seed', 'label' => 'Seed', 'type' => 'text', 'category' => 'advanced', 'applicable_to' => 'image', 'sort_order' => 32,
                'description' => 'Use same seed to reproduce results. Leave blank for random'],
            ['key' => 'model', 'label' => 'AI Model', 'type' => 'select', 'category' => 'advanced', 'applicable_to' => 'image', 'sort_order' => 33,
                'options' => ['dall-e-3' => 'DALL-E 3 (OpenAI)', 'dall-e-2' => 'DALL-E 2 (OpenAI)', 'stable-diffusion-xl' => 'Stable Diffusion XL', 'stable-diffusion-3' => 'Stable Diffusion 3', 'flux-pro' => 'Flux Pro', 'flux-dev' => 'Flux Dev'],
                'default_value' => 'dall-e-3'],
        ];

        foreach ($attributes as $attr) {
            \App\Models\GenerationAttribute::updateOrCreate(
                ['key' => $attr['key']],
                array_merge($attr, ['is_active' => true])
            );
        }
    }
}
