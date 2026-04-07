<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    // This column is added after teams table exists — no FK constraint here.
    // Relationship is enforced at application layer.
    public function up(): void {
        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedBigInteger('current_team_id')->nullable();
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('current_team_id');
        });
    }
};
