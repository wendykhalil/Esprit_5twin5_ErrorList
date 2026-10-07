<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['remise', 'retour']);
            $table->enum('etat', ['neuf', 'bon', 'use', 'endommage']);
            $table->unsignedTinyInteger('niveau_batterie')->nullable();
            $table->text('observations')->nullable();
            $table->dateTime('date_inspection');
            $table->timestamps();

            $table->unique(['reservation_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
