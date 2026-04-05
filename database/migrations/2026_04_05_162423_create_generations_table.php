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
        Schema::create('generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['image', 'video'])->default('image');
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->text('prompt');
            $table->text('negative_prompt')->nullable();
            $table->string('model')->default('dall-e-3');
            $table->string('provider')->default('openai'); // openai, stability, replicate
            $table->json('attributes')->nullable();
            $table->string('result_url')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('steps')->nullable();
            $table->float('guidance_scale')->nullable();
            $table->string('seed')->nullable();
            $table->integer('credits_used')->default(1);
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generations');
    }
};
