<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class FixReservation13PaymentId extends Command
{
    protected $signature = 'fix:reservation-13-payment-id {--confirm}';
    protected $description = 'Fix Payment #13 by setting reservation_id to 13 (requires --confirm flag)';

    public function handle()
    {
        $this->line('');
        $this->line('=== CORRECTION DE PAIEMENT #13 ===');
        $this->line('');

        // Verify current state
        $payment = Payment::find(13);
        if (!$payment) {
            $this->error('✗ Paiement #13 introuvable');
            return;
        }

        $this->line('État ACTUEL du Paiement #13:');
        $this->line('-' . str_repeat('-', 70));
        $this->line("  ID: {$payment->id}");
        $this->line("  Reservation ID: " . ($payment->reservation_id ?? 'NULL'));
        $this->line("  User ID: {$payment->user_id}");
        $this->line("  Amount: {$payment->amount} TND");
        $this->line("  Method: {$payment->method}");
        $this->line("  Status: {$payment->status}");
        $this->line("  Created: {$payment->created_at}");
        $this->line('');

        $this->line('Requête SQL à EXÉCUTER:');
        $this->line('-' . str_repeat('-', 70));
        $this->line("UPDATE payments SET reservation_id = 13 WHERE id = 13 AND reservation_id IS NULL;");
        $this->line('');

        $this->line('Paramètres de sécurité:');
        $this->line('-' . str_repeat('-', 70));
        $this->line('  ✓ WHERE id = 13 (cible uniquement ce paiement)');
        $this->line('  ✓ AND reservation_id IS NULL (cible uniquement les données corrompues)');
        $this->line('  ✓ Aucun autre champ modifié (amount, status, method, user_id inchangés)');
        $this->line('  ✓ Aucune transaction affectée');
        $this->line('');

        // Confirm flag
        if (!$this->option('confirm')) {
            $this->warn('ATTENTION: Requête préparée mais NON EXÉCUTÉE');
            $this->info('Pour exécuter la correction, relancer avec --confirm flag:');
            $this->line('  php artisan fix:reservation-13-payment-id --confirm');
            $this->line('');
            return;
        }

        // Execute with safety checks
        $this->line('Exécution de la correction (avec flag --confirm)...');
        $this->line('-' . str_repeat('-', 70));

        try {
            $affected = DB::table('payments')
                ->where('id', 13)
                ->whereNull('reservation_id')
                ->update(['reservation_id' => 13]);

            if ($affected === 1) {
                $this->info("✓ Correction appliquée avec succès ({$affected} ligne modifiée)");
                
                // Verify correction
                $updated = Payment::find(13);
                $this->line('');
                $this->line('État APRÈS correction:');
                $this->line('-' . str_repeat('-', 70));
                $this->line("  ID: {$updated->id}");
                $this->line("  Reservation ID: {$updated->reservation_id} ✓");
                $this->line("  User ID: {$updated->user_id} (inchangé)");
                $this->line("  Amount: {$updated->amount} TND (inchangé)");
                $this->line("  Method: {$updated->method} (inchangé)");
                $this->line("  Status: {$updated->status} (inchangé)");
                $this->line('');
                $this->info('✓ Correction vérifiée et confirmée');
            } else if ($affected === 0) {
                $this->warn("⚠️  Aucune ligne modifiée - vérifier l'état du paiement");
                $this->line("Possibilités:");
                $this->line("  - reservation_id n'est plus NULL");
                $this->line("  - Paiement #13 n'existe plus");
            } else {
                $this->error("✗ Erreur: {$affected} lignes modifiées (attendu: 1)");
            }
        } catch (\Exception $e) {
            $this->error('✗ Erreur lors de l\'exécution:');
            $this->error($e->getMessage());
        }

        $this->line('');
    }
}
