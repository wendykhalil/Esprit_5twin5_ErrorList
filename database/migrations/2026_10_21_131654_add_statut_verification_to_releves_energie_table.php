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
        Schema::table('releves_energie', function (Blueprint $table) {
            // Columns already exist from initial creation migration (2026_10_15_120000)
            // This migration is a no-op to maintain migration history compatibility
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: columns are managed by initial creation migration (2026_10_15_120000)
    }
};
