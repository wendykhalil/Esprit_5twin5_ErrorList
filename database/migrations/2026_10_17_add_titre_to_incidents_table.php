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
        Schema::table('incidents', function (Blueprint $table) {
            // Add titre column after type_probleme
            // This allows users to specify a problem title (e.g., "Chargeur manquant")
            // distinct from the full description
            $table->string('titre', 255)
                ->nullable()
                ->after('type_probleme')
                ->comment('Titre court du problème (ex: "Chargeur solaire manquant")');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropColumn('titre');
        });
    }
};
