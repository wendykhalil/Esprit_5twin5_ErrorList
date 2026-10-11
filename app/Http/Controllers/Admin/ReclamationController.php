<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveReclamationRequest;
use App\Http\Requests\RejectReclamationRequest;
use App\Models\Reclamation;
use App\Models\Incident;
use App\Models\Transaction;
use App\Services\ImmediateTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ReclamationController extends Controller
{
    /**
     * Display a listing of reclamations.
     */
    public function index(Request $request)
    {
        $query = Reclamation::with(['user', 'reservation.equipment', 'incidents']);

        // Filtrer par statut si demandé
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filtrer par utilisateur si demandé
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        $reclamations = $query->orderByDesc('created_at')->paginate(15);

        return view('backend.reclamations.index', compact('reclamations'));
    }

    /**
     * Display the specified reclamation.
     */
    public function show(Reclamation $reclamation)
    {
        $reclamation->load(['user', 'reservation.equipment', 'incidents.equipment']);

        $totalProposedRefund = $reclamation->calculateTotalRefund();

        return view('backend.reclamations.show', compact('reclamation', 'totalProposedRefund'));
    }

    /**
     * Show the form for approving a reclamation.
     */
    public function approveForm(Reclamation $reclamation)
    {
        $this->authorize('approve', $reclamation);

        $reclamation->load(['incidents.equipment', 'reservation', 'user']);

        $totalProposedRefund = $reclamation->calculateTotalRefund();

        return view('backend.reclamations.approve', compact('reclamation', 'totalProposedRefund'));
    }

    /**
     * Approve a reclamation and create refund transaction.
     * 
     * Atomic operation ensuring:
     * - Reclamation status is checked INSIDE transaction (idempotent)
     * - Refund reference is UUID-based (collision-resistant)
     * - Cannot exceed remaining refundable amount across ALL reclamations for same Payment
     * - All-or-nothing transaction
     * 
     * Cross-reclamation race condition prevention:
     * Uses SQLite IMMEDIATE transaction via separate connection. This ensures:
     * - Write lock acquired immediately (not deferred)
     * - Concurrent approvals for same Payment are serialized
     * - Cumulative refund limit enforced atomically
     * - Automatic rollback on exception (idempotent status check prevents duplicates)
     */
    public function approve(ApproveReclamationRequest $request, Reclamation $reclamation)
    {
        $this->authorize('approve', $reclamation);

        $validated = $request->validated();

        try {
            // Use IMMEDIATE transaction to serialize concurrent approvals for same Payment.
            // Same-connection BEGIN IMMEDIATE on Laravel's PDO: the status update,
            // refund insert, and cumulative check below are atomic.
            $immediateTransaction = new ImmediateTransaction();

            $result = $immediateTransaction->run(function () use ($validated, $reclamation) {
                // Re-read reclamation from DB to ensure we have current state
                $reclamation = Reclamation::find($reclamation->id);
                if (!$reclamation) {
                    throw new \Exception('Réclamation not found.');
                }

                // IDEMPOTENCY CHECK: Prevent duplicate approval
                // If status changed since policy check, this will fail
                if ($reclamation->status !== 'en_attente' && $reclamation->status !== 'en_cours') {
                    throw new \Exception('Cette réclamation ne peut plus être approuvée (statut: ' . $reclamation->status . ').');
                }

                // Calculate total refund amount
                $totalRefundAmount = 0;

                foreach ($validated['approved_incidents'] as $incidentId) {
                    $incident = Incident::findOrFail($incidentId);

                    if ($incident->reclamation_id !== $reclamation->id) {
                        throw new \Exception('Incident does not belong to this reclamation.');
                    }

                    $totalRefundAmount += $incident->proposed_refund_amount;
                }

                // Verify reclamation can be approved
                if (!$reclamation->canBeApproved()) {
                    throw new \Exception('Cette réclamation ne peut pas être approuvée (paiement manquant ou limite dépassée).');
                }

                // Get payment
                $payment = $reclamation->payment();
                if (!$payment) {
                    throw new \Exception('Aucun paiement trouvé pour cette réservation.');
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
                    throw new \Exception('Reclamation status changed during approval. Please try again.');
                }

                // Create refund transaction INSIDE IMMEDIATE lock
                $transaction = Transaction::create([
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
                    throw new \Exception(
                        'Refund total (' . $totalRefunded . ') exceeds payment amount (' . $payment->amount . ').'
                    );
                }

                return Reclamation::find($reclamation->id);
            });

            return redirect()->route('admin.reclamations.show', $result->id)
                ->with('success', 'Réclamation approuvée et remboursement créé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de l\'approbation : ' . $e->getMessage());
        }
    }

    /**
     * Show the form for rejecting a reclamation.
     */
    public function rejectForm(Reclamation $reclamation)
    {
        $this->authorize('reject', $reclamation);

        $reclamation->load(['incidents.equipment', 'reservation.equipment', 'user']);

        return view('backend.reclamations.reject', compact('reclamation'));
    }

    /**
     * Reject a reclamation.
     */
    public function reject(RejectReclamationRequest $request, Reclamation $reclamation)
    {
        $this->authorize('reject', $reclamation);

        $validated = $request->validated();

        try {
            DB::beginTransaction();

            $reclamation->update([
                'status' => 'rejetee',
                'rejected_reason' => $validated['rejected_reason'],
                'rejected_at' => now(),
                'admin_notes' => $validated['admin_notes'] ?? null,
            ]);

            DB::commit();

            return redirect()->route('admin.reclamations.show', $reclamation->id)
                ->with('success', 'Réclamation rejetée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors du rejet : ' . $e->getMessage());
        }
    }
}
