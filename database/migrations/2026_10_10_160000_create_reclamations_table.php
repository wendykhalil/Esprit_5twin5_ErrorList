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
        Schema::create('reclamations', function (Blueprint $table) {
            // Primary & Foreign Keys
            $table->id();
            $table->foreignId('reservation_id')
                ->constrained('reservations')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Claim Details
            $table->string('title')
                ->comment('Titre court de la réclamation');
            $table->text('description')
                ->comment('Description détaillée du problème');

            // Status & Workflow
            $table->enum('status', [
                'en_attente',
                'en_cours',
                'approuvee',
                'rejetee',
                'fermee',
            ])->default('en_attente')
                ->comment('État actuel de la réclamation');

            // Refund Information
            $table->decimal('refund_amount', 10, 2)
                ->nullable()
                ->comment('Montant du remboursement approuvé (TND)');
            $table->timestamp('refund_approved_at')
                ->nullable()
                ->comment('Quand l\'admin a approuvé le remboursement');

            // Admin Notes & Rejection
            $table->text('admin_notes')
                ->nullable()
                ->comment('Notes de l\'administrateur pendant l\'examen');
            $table->text('rejected_reason')
                ->nullable()
                ->comment('Raison du rejet (si applicable)');
            $table->timestamp('rejected_at')
                ->nullable()
                ->comment('Quand la réclamation a été rejetée');

            // Timestamps
            $table->timestamps();

            // Indexes for common queries (but not for FKs - they get auto-indexed)
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reclamations');
    }
};
