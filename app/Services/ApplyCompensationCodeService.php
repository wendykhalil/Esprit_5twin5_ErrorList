<?php

namespace App\Services;

use App\Models\CompensationCode;
use App\Models\Reclamation;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * ApplyCompensationCodeService
 *
 * Handles atomic application of compensation codes to approved reclamations.
 * Ensures:
 * - Reclamation status is updated to 'approuvee'
 * - Compensation code is generated and assigned
 * - All operations are atomic (all-or-nothing)
 * - No duplicate codes for the same reclamation
 */
class ApplyCompensationCodeService
{
    /**
     * Apply a compensation code to a reclamation.
     *
     * @param Reclamation $reclamation
     * @param array $validated Data containing: type, value, days_valid, max_uses, admin_notes
     * @param int|null $createdBy Admin user ID who created this
     * @return CompensationCode The created compensation code
     * @throws Exception If the operation cannot be completed
     */
    public function apply(
        Reclamation $reclamation,
        array $validated,
        ?int $createdBy = null
    ): CompensationCode {
        return DB::transaction(function () use ($reclamation, $validated, $createdBy) {
            // Re-fetch reclamation to ensure we have the latest state
            $reclamation = Reclamation::lockForUpdate()->find($reclamation->id);

            if (!$reclamation) {
                throw new Exception('Réclamation non trouvée.');
            }

            // Check reclamation can receive compensation
            if (!$reclamation->canReceiveCompensation()) {
                throw new Exception(
                    'Cette réclamation ne peut pas recevoir de compensation ' .
                    '(statut: ' . $reclamation->status . ' ou code déjà attribué).'
                );
            }

            // Check if reclamation already has a compensation code (race condition check)
            if ($reclamation->compensationCode !== null) {
                throw new Exception('Un code de compensation a déjà été attribué à cette réclamation.');
            }

            // Update reclamation status to 'approuvee'
            $rowsUpdated = DB::table('reclamations')
                ->where('id', $reclamation->id)
                ->whereIn('status', ['en_attente', 'en_cours'])
                ->update([
                    'status' => 'approuvee',
                    'admin_notes' => $validated['admin_notes'] ?? null,
                    'updated_at' => now(),
                ]);

            if ($rowsUpdated !== 1) {
                throw new Exception(
                    'Impossible de mettre à jour le statut de la réclamation. ' .
                    'Le statut a peut-être changé pendant cette opération.'
                );
            }

            // Create compensation code using the static factory method
            $compensationCode = CompensationCode::createFromReclamation(
                reclamation: Reclamation::find($reclamation->id), // Refresh to get updated status
                type: $validated['type'],
                value: (float) $validated['value'],
                daysValid: (int) $validated['days_valid'],
                maxUses: (int) $validated['max_uses'],
                createdBy: $createdBy
            );

            return $compensationCode;
        });
    }

    /**
     * Reject a reclamation (set status to 'rejetee').
     *
     * @param Reclamation $reclamation
     * @param string $rejectedReason
     * @param string|null $adminNotes
     * @return Reclamation The updated reclamation
     * @throws Exception If the operation cannot be completed
     */
    public function reject(
        Reclamation $reclamation,
        string $rejectedReason,
        ?string $adminNotes = null
    ): Reclamation {
        return DB::transaction(function () use ($reclamation, $rejectedReason, $adminNotes) {
            // Re-fetch reclamation to ensure we have the latest state
            $reclamation = Reclamation::lockForUpdate()->find($reclamation->id);

            if (!$reclamation) {
                throw new Exception('Réclamation non trouvée.');
            }

            // Only en_attente or en_cours can be rejected
            if (!in_array($reclamation->status, ['en_attente', 'en_cours'])) {
                throw new Exception(
                    'Cette réclamation ne peut pas être rejetée (statut: ' . $reclamation->status . ').'
                );
            }

            $reclamation->update([
                'status' => 'rejetee',
                'rejected_reason' => $rejectedReason,
                'rejected_at' => now(),
                'admin_notes' => $adminNotes,
            ]);

            return $reclamation;
        });
    }
}
