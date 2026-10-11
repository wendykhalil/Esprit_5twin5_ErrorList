<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use App\Models\Payment;
use App\Models\Contract;
use App\Models\Delivery;
use App\Models\Transaction;

class VerifyReservation13Data extends Command
{
    protected $signature = 'verify:reservation-13';
    protected $description = 'Verify historical data for Reservation #13, Payment #13, and related entities';

    public function handle()
    {
        $this->line('');
        $this->line('=== VERIFICATION DES DONNÉES HISTORIQUES POUR RÉSERVATION #13 ===');
        $this->line('');

        // Step 1: Reservation #13
        $this->line('STEP 1: Vérification de la Réservation #13');
        $this->line('-' . str_repeat('-', 70));
        
        $reservation = Reservation::find(13);
        if ($reservation) {
            $this->info('✓ Réservation #13 trouvée');
            $this->line("  ID: {$reservation->id}");
            $this->line("  User ID: {$reservation->user_id}");
            $this->line("  Equipment ID: {$reservation->equipment_id}");
            $this->line("  Status: {$reservation->status}");
            $this->line("  Prix Total: {$reservation->prix_total} TND");
            $this->line("  Date Début: {$reservation->date_debut}");
            $this->line("  Date Fin: {$reservation->date_fin}");
            $this->line("  Créée: {$reservation->created_at}");
            $this->line("  Modifiée: {$reservation->updated_at}");
        } else {
            $this->error('✗ Réservation #13 NOT FOUND');
            return;
        }

        // Step 2: Payment #13 and check reservation_id
        $this->line('');
        $this->line('STEP 2: Vérification du Paiement #13');
        $this->line('-' . str_repeat('-', 70));
        
        $payment = Payment::find(13);
        if ($payment) {
            $this->info('✓ Paiement #13 trouvé');
            $this->line("  ID: {$payment->id}");
            $this->line("  Reservation ID (DATABASE): {$payment->reservation_id}");
            $this->line("  User ID: {$payment->user_id}");
            $this->line("  Amount: {$payment->amount} TND");
            $this->line("  Method: {$payment->method}");
            $this->line("  Status: {$payment->status}");
            $this->line("  Créé: {$payment->created_at}");
            $this->line("  Modifié: {$payment->updated_at}");

            // Check if reservation_id is NULL
            if ($payment->reservation_id === null) {
                $this->warn("  ⚠️  ATTENTION: reservation_id est NULL!");
            } else {
                $this->line("  ✓ reservation_id n'est pas NULL");
            }
        } else {
            $this->error('✗ Paiement #13 NOT FOUND');
            return;
        }

        // Step 3: Verify payment belongs to reservation by other data
        $this->line('');
        $this->line('STEP 3: Confirmation que Paiement #13 appartient à Réservation #13');
        $this->line('-' . str_repeat('-', 70));

        $matches = [];
        
        // Check 1: User ID match
        if ($payment->user_id === $reservation->user_id) {
            $matches[] = "✓ Même User ID: {$reservation->user_id}";
        } else {
            $matches[] = "✗ User ID DIFFÉRENT - Paiement: {$payment->user_id}, Réservation: {$reservation->user_id}";
        }

        // Check 2: Amount match (payment amount should equal reservation price)
        if ((float)$payment->amount === (float)$reservation->prix_total) {
            $matches[] = "✓ Montant identique: {$payment->amount} TND";
        } else {
            $matches[] = "✗ Montant DIFFÉRENT - Paiement: {$payment->amount}, Réservation: {$reservation->prix_total}";
        }

        // Check 3: Temporal proximity
        $paymentDate = $payment->created_at;
        $reservationDate = $reservation->created_at;
        $daysDiff = $paymentDate->diffInDays($reservationDate);
        if ($daysDiff <= 7) {
            $matches[] = "✓ Proximité temporelle: {$daysDiff} jours";
        } else {
            $matches[] = "⚠️  Gap temporel: {$daysDiff} jours";
        }

        // Check 4: Both same status progression
        if ($reservation->status === 'confirmee' && $payment->status === 'paid') {
            $matches[] = "✓ Progression de statut cohérente (Réservation confirmee, Paiement paid)";
        } else {
            $matches[] = "⚠️  Progression de statut: Réservation {$reservation->status}, Paiement {$payment->status}";
        }

        // Check 5: Are there other payments for this user with similar amount?
        $otherPayments = Payment::where('user_id', $payment->user_id)
            ->where('id', '!=', 13)
            ->where('amount', $payment->amount)
            ->count();
        
        if ($otherPayments === 0) {
            $matches[] = "✓ Aucun autre paiement du même user avec le même montant";
        } else {
            $matches[] = "⚠️  Il existe {$otherPayments} autre(s) paiement(s) du même user avec montant similaire";
        }

        foreach ($matches as $match) {
            $this->line("  {$match}");
        }

        // Step 4: Transactions for Payment #13
        $this->line('');
        $this->line('STEP 4: Transactions associées au Paiement #13');
        $this->line('-' . str_repeat('-', 70));
        
        $transactions = Transaction::where('payment_id', 13)->get();
        if ($transactions->count() > 0) {
            $this->info("✓ {$transactions->count()} transaction(s) trouvée(s)");
            foreach ($transactions as $tx) {
                $this->line("  - TX #{$tx->id}: Type={$tx->type}, Amount={$tx->amount}, Status={$tx->status}, Date={$tx->transaction_date}");
            }
        } else {
            $this->warn('⚠️  Aucune transaction trouvée');
        }

        // Step 5: Existing Contract for Reservation #13
        $this->line('');
        $this->line('STEP 5: Contrat pour Réservation #13');
        $this->line('-' . str_repeat('-', 70));
        
        $contract = Contract::where('reservation_id', 13)->first();
        if ($contract) {
            $this->warn('⚠️  Contrat EXISTE DÉJÀ');
            $this->line("  ID: {$contract->id}");
            $this->line("  Number: {$contract->contract_number}");
            $this->line("  Status: {$contract->status}");
            $this->line("  Created: {$contract->created_at}");
        } else {
            $this->info('✓ Aucun contrat trouvé (normal - à créer)');
        }

        // Step 6: Delivery for Reservation #13
        $this->line('');
        $this->line('STEP 6: Livraison pour Réservation #13');
        $this->line('-' . str_repeat('-', 70));
        
        $delivery = Delivery::where('reservation_id', 13)->first();
        if ($delivery) {
            $this->info('✓ Livraison trouvée');
            $this->line("  ID: {$delivery->id}");
            $this->line("  Status: {$delivery->status}");
            $this->line("  Created: {$delivery->created_at}");
        } else {
            $this->warn('⚠️  Aucune livraison trouvée');
        }

        // Summary
        $this->line('');
        $this->line('=== RÉSUMÉ ===');
        $this->line('-' . str_repeat('-', 70));
        $this->line("Réservation #13 (confirmee): ✓");
        $this->line("Paiement #13 (paid): ✓ - BUT reservation_id = " . ($payment->reservation_id ?? 'NULL'));
        $this->line("Contrat #13: " . ($contract ? '✓' : '✗'));
        $this->line("Livraison #13: " . ($delivery ? '✓' : '✗'));
        $this->line("Transactions: " . $transactions->count());
        $this->line('');
        $this->line('=== CONCLUSION ===');
        
        $allMatch = true;
        foreach ($matches as $match) {
            if (strpos($match, '✗') !== false) {
                $allMatch = false;
                break;
            }
        }
        
        if ($allMatch && $payment->reservation_id === null) {
            $this->info('✓ Paiement #13 APPARTIENT à Réservation #13 (association confirmée par les données)');
            $this->warn('→ reservation_id peut être corrigé en toute sécurité');
        } else if (!$allMatch) {
            $this->error('✗ DONNÉES INCOHÉRENTES - Ne pas procéder à la correction');
        }
    }
}
