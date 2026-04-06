<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $videoAttributes = [
            // Basic
            [
                'key' => 'video_model', 'label' => 'AI Model', 'type' => 'select',
                'category' => 'basic', 'applicable_to' => 'video', 'sort_order' => 1,
                'options' => json_encode([
                    'veo-3.1'            => 'Google Veo 3.1 ✨',
                    'sora-1.0-hd'        => 'OpenAI Sora HD',
                    'sora-1.0'           => 'OpenAI Sora',
                    'eleven_video_v1'    => 'ElevenLabs Video v1',
                ]),
                'default_value' => 'veo-3.1',
                'description' => 'Model determines the provider automatically',
                'is_active' => true,
            ],
            [
                'key' => 'duration', 'label' => 'Duration (seconds)', 'type' => 'select',
                'category' => 'basic', 'applicable_to' => 'video', 'sort_order' => 2,
                'options' => json_encode([
                    '5' => '5 seconds', '8' => '8 seconds',
                    '10' => '10 seconds', '15' => '15 seconds', '30' => '30 seconds',
                ]),
                'default_value' => '5',
                'is_active' => true,
            ],
            [
                'key' => 'video_resolution', 'label' => 'Resolution', 'type' => 'select',
                'category' => 'basic', 'applicable_to' => 'video', 'sort_order' => 3,
                'options' => json_encode(['480p' => '480p', '720p' => '720p HD', '1080p' => '1080p Full HD']),
                'default_value' => '1080p',
                'is_active' => true,
            ],

            // Style
            [
                'key' => 'video_style', 'label' => 'Visual Style', 'type' => 'select',
                'category' => 'style', 'applicable_to' => 'video', 'sort_order' => 10,
                'options' => json_encode([
                    'cinematic'    => 'Cinematic',
                    'photorealistic' => 'Photorealistic',
                    'animated'     => 'Animated',
                    'documentary'  => 'Documentary',
                    'nature'       => 'Nature / Wildlife',
                    'abstract'     => 'Abstract',
                    'vintage'      => 'Vintage / Film',
                ]),
                'default_value' => 'cinematic',
                'is_active' => true,
            ],
            [
                'key' => 'motion_intensity', 'label' => 'Motion Intensity', 'type' => 'select',
                'category' => 'style', 'applicable_to' => 'video', 'sort_order' => 11,
                'options' => json_encode([
                    'static'  => 'Mostly static',
                    'slow'    => 'Slow & smooth',
                    'normal'  => 'Normal',
                    'dynamic' => 'Dynamic',
                    'fast'    => 'Fast & energetic',
                ]),
                'default_value' => 'normal',
                'is_active' => true,
            ],
            [
                'key' => 'camera_motion', 'label' => 'Camera Movement', 'type' => 'select',
                'category' => 'style', 'applicable_to' => 'video', 'sort_order' => 12,
                'options' => json_encode([
                    'static'   => 'Static / Fixed',
                    'pan'      => 'Pan',
                    'zoom_in'  => 'Zoom In',
                    'zoom_out' => 'Zoom Out',
                    'tracking' => 'Tracking shot',
                    'aerial'   => 'Aerial / Drone',
                    'handheld' => 'Handheld',
                ]),
                'default_value' => 'static',
                'is_active' => true,
            ],

            // Quality
            [
                'key' => 'video_quality', 'label' => 'Quality', 'type' => 'select',
                'category' => 'quality', 'applicable_to' => 'video', 'sort_order' => 20,
                'options' => json_encode(['draft' => 'Draft (fast)', 'standard' => 'Standard', 'high' => 'High']),
                'default_value' => 'high',
                'is_active' => true,
            ],
            [
                'key' => 'fps', 'label' => 'Frame Rate', 'type' => 'select',
                'category' => 'quality', 'applicable_to' => 'video', 'sort_order' => 21,
                'options' => json_encode(['24' => '24 fps (cinematic)', '30' => '30 fps', '60' => '60 fps (smooth)']),
                'default_value' => '24',
                'is_active' => true,
            ],
        ];

        foreach ($videoAttributes as $attr) {
            \App\Models\GenerationAttribute::updateOrCreate(
                ['key' => $attr['key']],
                $attr
            );
        }

        // Update aspect_ratio to apply to 'both' (was 'both' already, this ensures it)
        \App\Models\GenerationAttribute::where('key', 'aspect_ratio')
            ->update(['applicable_to' => 'both']);
    }

    public function down(): void
    {
        \App\Models\GenerationAttribute::whereIn('key', [
            'video_model', 'duration', 'video_resolution',
            'video_style', 'motion_intensity', 'camera_motion',
            'video_quality', 'fps',
        ])->delete();
    }
};
