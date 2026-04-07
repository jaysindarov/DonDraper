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
            // JSON array of all product image paths (multiple angles).
            // product_image_path (single string) is kept as the primary/first image
            // so existing records and UI display code continues to work unchanged.
            $table->json('product_image_paths')->nullable()->after('product_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('generations', function (Blueprint $table) {
            $table->dropColumn('product_image_paths');
        });
    }
};
