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
        Schema::table('payments', function (Blueprint $table) {
            // Ajouter reservation_id avec FK vers reservations
            // nullable pour compatibilité avec les paiements existants
            $table->foreignId('reservation_id')
                ->nullable()
                ->after('user_id')
                ->constrained('reservations')
                ->onDelete('set null');
            
            // Index unique : une seule réservation en attente de paiement
            // (empêche les paiements en double)
            $table->unique(['reservation_id', 'status'], 'unique_pending_payment_per_reservation')
                ->where('status', '!=', 'refunded');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('unique_pending_payment_per_reservation');
            $table->dropForeignIdFor('reservations');
            $table->dropColumn('reservation_id');
        });
    }
};
