<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('image')->nullable();
            $table->enum('critere', [
                'kwh_total',
                'co2_total',
                'reservations_terminees',
                'retours_sans_dommage',
                'releves_declares',
            ]);
            $table->unsignedInteger('seuil');
            $table->enum('niveau', ['bronze', 'argent', 'or']);
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
