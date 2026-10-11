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
        Schema::create('incidents', function (Blueprint $table) {
            // Primary & Foreign Keys
            $table->id();
            $table->foreignId('reclamation_id')
                ->constrained('reclamations')
                ->cascadeOnDelete();
            $table->foreignId('equipment_id')
                ->constrained('equipment')
                ->cascadeOnDelete();

            // Problem Classification
            $table->enum('type_probleme', [
                'endommage',
                'manquant',
                'malfonctionnant',
                'autre',
            ])->comment('Type de problème détecté');

            $table->text('description')
                ->comment('Description détaillée du problème');

            // Damage Assessment (Admin input)
            $table->float('damage_percentage')
                ->nullable()
                ->comment('Pourcentage de dommage évalué par l\'admin (0-100)');

            // Refund Calculation
            $table->float('proposed_refund_percentage')
                ->comment('Pourcentage de remboursement proposé (30%, 20%, ou custom)');

            $table->decimal('proposed_refund_amount', 10, 2)
                ->comment('Montant du remboursement proposé (TND, snapshot du prix à la création)');

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index('reclamation_id');
            $table->index('equipment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
