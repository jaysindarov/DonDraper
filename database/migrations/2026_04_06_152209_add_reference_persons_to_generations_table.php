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
        Schema::table('generations', function (Blueprint $table) {
            // Stores [{path, name}, ...] — up to 2 reference people
            $table->json('reference_persons')->nullable()->after('product_image_path');
        });

        // Add gpt-image-1 to the top of the model selector options
        \App\Models\GenerationAttribute::where('key', 'model')->update([
            'options' => json_encode([
                'gpt-image-1'          => 'GPT Image 1 (Best Quality ✨)',
                'dall-e-3'             => 'DALL-E 3 (OpenAI)',
                'dall-e-2'             => 'DALL-E 2 (OpenAI)',
                'stable-diffusion-xl'  => 'Stable Diffusion XL',
                'stable-diffusion-3'   => 'Stable Diffusion 3',
                'flux-pro'             => 'Flux Pro',
                'flux-dev'             => 'Flux Dev',
            ]),
            'default_value' => 'gpt-image-1',
        ]);
    }

    public function down(): void
    {
        Schema::table('generations', function (Blueprint $table) {
            $table->dropColumn('reference_persons');
        });
    }
};
