<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use App\Models\Contract;

class CreateContractForReservation13 extends Command
{
    protected $signature = 'create:contract-for-res13 {--confirm}';
    protected $description = 'Create contract for Reservation #13 if it does not exist (idempotent)';

    public function handle()
    {
        $this->line('');
        $this->line('=== CRÉATION DE CONTRAT POUR RÉSERVATION #13 ===');
        $this->line('');

        // Step 1: Check reservation
        $reservation = Reservation::find(13);
        if (!$reservation) {
            $this->error('✗ Réservation #13 introuvable');
            return;
        }

        $this->line('Réservation #13:');
        $this->line('-' . str_repeat('-', 70));
        $this->line("  ID: {$reservation->id}");
        $this->line("  User ID: {$reservation->user_id}");
        $this->line("  Equipment ID: {$reservation->equipment_id}");
        $this->line("  Status: {$reservation->status}");
        $this->line("  Prix Total: {$reservation->prix_total} TND");
        $this->line("  Date Début: {$reservation->date_debut}");
        $this->line("  Date Fin: {$reservation->date_fin}");
        $this->line('');

        // Step 2: Check if payment exists and has reservation_id
        $payment = $reservation->payments()->first();
        if (!$payment) {
            $this->error('✗ Aucun paiement trouvé pour cette réservation');
            return;
        }

        $this->line('Paiement associé:');
        $this->line('-' . str_repeat('-', 70));
        $this->line("  ID: {$payment->id}");
        $this->line("  Reservation ID: {$payment->reservation_id}");
        $this->line("  Amount: {$payment->amount} TND");
        $this->line("  Status: {$payment->status}");
        $this->line('');

        // Step 3: Check if contract already exists
        $existingContract = Contract::where('reservation_id', 13)->first();
        
        $this->line('État du contrat:');
        $this->line('-' . str_repeat('-', 70));
        
        if ($existingContract) {
            $this->warn('⚠️  Contrat EXISTE DÉJÀ');
            $this->line("  ID: {$existingContract->id}");
            $this->line("  Number: {$existingContract->contract_number}");
            $this->line("  Status: {$existingContract->status}");
            $this->line("  Amount: {$existingContract->amount} TND");
            $this->line("  Created: {$existingContract->created_at}");
            $this->info('✓ Idempotence respected - aucun nouveau contrat créé');
            $this->line('');
            return;
        }

        $this->info('✓ Aucun contrat existant');
        $this->line('');

        // Step 4: Show what will be created
        $this->line('Contrat à CRÉER:');
        $this->line('-' . str_repeat('-', 70));
        $this->line("  Reservation ID: {$reservation->id}");
        $this->line("  User ID: {$reservation->user_id}");
        $this->line("  Equipment ID: {$reservation->equipment_id}");
        $this->line("  Contract Number: CONT-YYYY-XXXXXX (généré automatiquement)");
        $this->line("  Start Date: {$reservation->date_debut}");
        $this->line("  End Date: {$reservation->date_fin}");
        $this->line("  Amount: {$payment->amount} TND");
        $this->line("  Status: active");
        $this->line("  Terms & Conditions: (générées automatiquement)");
        $this->line('');

        // Confirm flag
        if (!$this->option('confirm')) {
            $this->warn('ATTENTION: Contrat préparé mais NON CRÉÉ');
            $this->info('Pour créer le contrat, relancer avec --confirm flag:');
            $this->line('  php artisan create:contract-for-res13 --confirm');
            $this->line('');
            return;
        }

        // Create contract
        $this->line('Création du contrat (avec flag --confirm)...');
        $this->line('-' . str_repeat('-', 70));

        try {
            // Use the same logic as PaymentController
            if ($reservation->contract()->exists()) {
                $this->warn('⚠️  Contrat existe déjà (idempotence check)');
                return;
            }

            $contract = Contract::create([
                'reservation_id' => $reservation->id,
                'user_id' => $reservation->user_id,
                'equipment_id' => $reservation->equipment_id,
                'contract_number' => Contract::generateContractNumber(),
                'start_date' => $reservation->date_debut,
                'end_date' => $reservation->date_fin,
                'amount' => $payment->amount,
                'status' => 'active',
                'terms_and_conditions' => $this->generateTermsAndConditions($reservation),
            ]);

            $this->info('✓ Contrat créé avec succès');
            $this->line('');
            $this->line('Détails du contrat créé:');
            $this->line('-' . str_repeat('-', 70));
            $this->line("  ID: {$contract->id}");
            $this->line("  Number: {$contract->contract_number}");
            $this->line("  Status: {$contract->status}");
            $this->line("  Amount: {$contract->amount} TND");
            $this->line("  Created: {$contract->created_at}");
            $this->line('');
            $this->info('✓ Contrat créé et vérifié');

        } catch (\Exception $e) {
            $this->error('✗ Erreur lors de la création du contrat:');
            $this->error($e->getMessage());
        }

        $this->line('');
    }

    /**
     * Generate standard terms and conditions
     */
    private function generateTermsAndConditions($reservation)
    {
        $startDate = $reservation->date_debut;
        $endDate = $reservation->date_fin;
        $equipmentName = $reservation->equipment?->name;
        $equipmentName = $equipmentName ?? 'N/A';
        
        return <<<EOT
TERMES ET CONDITIONS DE LOCATION

Parties:
- Bailleur: SolarShare SARL
- Locataire: (Voir identification utilisateur)
- Équipement: {$equipmentName}

Durée de location:
- Début: {$startDate}
- Fin: {$endDate}

Montant total: {$reservation->prix_total} TND

Conditions:
1. L'équipement doit être retourné en bon état
2. L'utilisateur est responsable de tout dommage causé
3. Respect du délai de retour obligatoire
4. Pénalités applicables en cas de retard

Signatures:
- Bailleur: ___________________
- Locataire: ___________________
EOT;
    }
}
