<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('generations', function (Blueprint $table): void {
            $table->boolean('is_public')->default(false)->after('metadata');
            $table->index(['is_public', 'status', 'type'], 'generations_gallery_index');
        });
    }
    public function down(): void {
        Schema::table('generations', function (Blueprint $table): void {
            $table->dropIndex('generations_gallery_index');
            $table->dropColumn('is_public');
        });
    }
};
