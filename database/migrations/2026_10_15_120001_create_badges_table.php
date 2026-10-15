<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nom');
            $table->enum('niveau', ['bronze', 'argent', 'or']);
            $table->unsignedInteger('seuil_kwh');
            $table->date('date_obtention');
            $table->timestamps();
            $table->unique(['user_id', 'nom']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
