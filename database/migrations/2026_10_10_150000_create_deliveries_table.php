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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Client
            $table->foreignId('equipment_id')->constrained()->cascadeOnDelete();

            // Planned and actual delivery/return dates
            $table->date('planned_delivery_date'); // When equipment should be picked up
            $table->dateTime('actual_delivery_date')->nullable(); // When actually delivered
            $table->date('planned_return_date'); // When equipment should be returned
            $table->dateTime('actual_return_date')->nullable(); // When actually returned

            // Status tracking
            $table->string('status')->default('prete'); // prete, remise_au_client, retour_reçu, terminée
            $table->text('notes')->nullable(); // General notes about delivery
            $table->text('delivery_notes')->nullable(); // Notes during delivery (condition, issues, etc.)
            $table->text('return_notes')->nullable(); // Notes during return (condition, damage, etc.)

            // Timestamps
            $table->timestamps();

            // Indexes for common queries
            $table->index('reservation_id');
            $table->index('user_id');
            $table->index('equipment_id');
            $table->index('status');
            $table->index('planned_delivery_date');
            $table->index('planned_return_date');

            // Unique constraint: one delivery per reservation (business rule)
            $table->unique('reservation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
