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
            $table->decimal('kwh_estimes', 8, 2);
            $table->decimal('co2_evite_kg', 8, 2);
            $table->date('date_releve');
            $table->text('observations')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('releves_energie');
    }
};
