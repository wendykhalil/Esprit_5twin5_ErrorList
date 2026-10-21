<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('releves_energie', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('reservations')->cascadeOnDelete()->unique();
            $table->unsignedInteger('puissance_watts')->nullable();
            $table->decimal('heures_utilisation', 6, 2);
            $table->decimal('kwh_estimes', 12, 4);
            $table->decimal('co2_evite_kg', 12, 4);
            $table->date('date_releve');
            $table->text('observations')->nullable();
            $table->enum('statut_verification', ['normal', 'suspect', 'valide', 'rejete'])->default('normal')->index();
            $table->text('motif_suspicion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('releves_energie');
    }
};
