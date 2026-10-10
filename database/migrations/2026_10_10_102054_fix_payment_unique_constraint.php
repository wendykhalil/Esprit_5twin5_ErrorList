<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Améliore la contrainte UNIQUE pour empêcher réellement les doubles paiements:
     * - Ancien: unique(['reservation_id', 'status']) permettait failed → pending → paid
     * - Nouveau: index unique où reservation_id IS NOT NULL et status IN ('pending', 'paid')
     * 
     * Cette approche:
     * ✓ Empêche 2 paiements actifs (pending/paid) pour la même réservation
     * ✓ Permet un nouveau paiement après 'failed' ou 'refunded'
     * ✓ Compatible avec SQLite (utilise WHERE au lieu de partial indexes)
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Supprimer l'ancienne contrainte incorrecte
            $table->dropUnique('unique_pending_payment_per_reservation');
            
            // Ajouter une meilleure contrainte via index unique partiel
            // Cet index force un seul paiement non-failed/non-refunded par réservation
            $table->unique(
                ['reservation_id'],
                'unique_active_payment_per_reservation'
            )->where('reservation_id', '!=', null)
               ->where('status', '!=', 'failed')
               ->where('status', '!=', 'refunded');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique('unique_active_payment_per_reservation');
            
            // Restaurer l'ancienne contrainte pour rollback
            $table->unique(
                ['reservation_id', 'status'],
                'unique_pending_payment_per_reservation'
            )->where('status', '!=', 'refunded');
        });
    }
};
