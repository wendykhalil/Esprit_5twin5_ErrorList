<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\Reclamation;
use App\Models\Transaction;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * ApproveReclamationService
 *
 * Extracts the approval business logic from
 * ReclamationController::approve() WITHOUT changing any business rules.
 *
 * The whole approval body runs inside ImmediateTransaction (SQLite
 * BEGIN IMMEDIATE on the default connection's own PDO):
 *  - conditional reclamation status update (idempotency guard),
 *  - refund Transaction insert (type=refund, status=completed),
 *  - cumulative completed-refund check
 *      SUM(transactions.amount)
 *      WHERE payment_id = ? AND type = 'refund' AND status = 'completed',
 *  - final limit check against payment->amount.
 *
 * All-or-nothing: any exception triggers ROLLBACK of both the status
 * change and the refund transaction insert.
 */
class ApproveReclamationService
{
    public function __construct(
        private readonly ImmediateTransaction $immediateTransaction = new ImmediateTransaction()
    ) {}

    /**
     * Approve a reclamation and create its refund transaction atomically.
     *
     * @param Reclamation $reclamation
     * @param array{approved_incidents: array<int>, admin_notes?: ?string} $validated
     * @return Reclamation Fresh reclamation model.
     *
     * @throws Exception When the reclamation cannot be approved.
     */
    public function approve(Reclamation $reclamation, array $validated): Reclamation
    {
        return $this->immediateTransaction->run(function () use ($validated, $reclamation) {
            // Re-read reclamation from DB to ensure we have current state
            $reclamation = Reclamation::find($reclamation->id);
            if (! $reclamation) {
                throw new Exception('Réclamation not found.');
            }

            // IDEMPOTENCY CHECK: Prevent duplicate approval
            // If status changed since policy check, this will fail
            if ($reclamation->status !== 'en_attente' && $reclamation->status !== 'en_cours') {
                throw new Exception('Cette réclamation ne peut plus être approuvée (statut: ' . $reclamation->status . ').');
            }

            // Calculate total refund amount
            $totalRefundAmount = 0;

            foreach ($validated['approved_incidents'] as $incidentId) {
                $incident = Incident::findOrFail($incidentId);

                if ($incident->reclamation_id !== $reclamation->id) {
                    throw new Exception('Incident does not belong to this reclamation.');
                }

                $totalRefundAmount += $incident->proposed_refund_amount;
            }

            // Verify reclamation can be approved
            if (! $reclamation->canBeApproved()) {
                throw new Exception('Cette réclamation ne peut pas être approuvée (paiement manquant ou limite dépassée).');
            }

            // Get payment
            $payment = $reclamation->payment();
            if (! $payment) {
                throw new Exception('Aucun paiement trouvé pour cette réservation.');
            }

            // ATOMIC CONDITIONAL UPDATE: Prevent duplicate approval via status change check
            // Only update if status is still eligible (en_attente or en_cours)
            // This closes the race condition window: if another request already updated status,
            // this UPDATE will affect 0 rows and throw an exception, causing rollback
            $rowsUpdated = DB::table('reclamations')
                ->where('id', $reclamation->id)
                ->whereIn('status', ['en_attente', 'en_cours'])
                ->update([
                    'status' => 'approuvee',
                    'refund_amount' => $totalRefundAmount,
                    'refund_approved_at' => now(),
                    'admin_notes' => $validated['admin_notes'] ?? null,
                    'updated_at' => now(),
                ]);

            // If no rows were updated, status must have changed (concurrent request won the race)
            if ($rowsUpdated !== 1) {
                throw new Exception('Reclamation status changed during approval. Please try again.');
            }

            // Create refund transaction INSIDE IMMEDIATE lock
            Transaction::create([
                'payment_id' => $payment->id,
                'reference' => 'REC-' . Str::uuid()->toString(),
                'type' => 'refund',
                'amount' => $totalRefundAmount,
                'status' => 'completed',
                'transaction_date' => now(),
                'description' => 'Remboursement pour réclamation #' . $reclamation->id,
            ]);

            // CUMULATIVE REFUND CHECK: Prevent over-refunding across all reclamations
            // This check happens INSIDE the IMMEDIATE transaction, after inserting this transaction
            // Concurrent transactions are serialized, so this accurately reflects the new total
            $totalRefunded = DB::table('transactions')
                ->where('payment_id', $payment->id)
                ->where('type', 'refund')
                ->where('status', 'completed')
                ->sum('amount');

            if ($totalRefunded > $payment->amount) {
                throw new Exception(
                    'Refund total (' . $totalRefunded . ') exceeds payment amount (' . $payment->amount . ').'
                );
            }

            return Reclamation::find($reclamation->id);
        });
    }
}
