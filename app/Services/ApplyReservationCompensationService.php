<?php

namespace App\Services;

use App\Models\CompensationCode;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

/**
 * Service to atomically apply a compensation code to a reservation.
 *
 * Responsibilities:
 * 1. Validate code ownership and validity (already done in FormRequest)
 * 2. Calculate discount atomically
 * 3. Increment code usage count safely (prevent race conditions)
 * 4. Store audit trail (base_price, discount_amount)
 * 5. Ensure final price never becomes negative
 */
class ApplyReservationCompensationService
{
    /**
     * Apply compensation code to a reservation.
     *
     * @param Reservation $reservation The reservation to apply code to (must be en_attente)
     * @param string $codeString The compensation code string to apply
     * @return Reservation Updated reservation with discount applied
     * @throws \Exception If code is invalid or application fails
     */
    public function apply(Reservation $reservation, string $codeString): Reservation
    {
        // Use transaction with serialization to prevent race conditions
        // Similar to ImmediateTransaction pattern used in ApproveReclamationService
        return DB::transaction(
            function () use ($reservation, $codeString) {
                // Lock the code for update (prevents concurrent use)
                $code = CompensationCode::where('code', trim($codeString))
                    ->lockForUpdate()
                    ->first();

                if (!$code) {
                    throw new \Exception('Le code de compensation n\'existe pas.');
                }

                // Validate ownership (should already be validated in FormRequest, but double-check)
                if ($code->user_id !== $reservation->user_id) {
                    throw new \Exception('Ce code de compensation ne vous appartient pas.');
                }

                // Validate code is still usable
                if (!$code->is_active) {
                    throw new \Exception('Ce code de compensation a été désactivé.');
                }

                if (!$code->hasStarted()) {
                    throw new \Exception('Ce code de compensation n\'est pas encore valide.');
                }

                if ($code->isExpired()) {
                    throw new \Exception('Ce code de compensation a expiré.');
                }

                if (!$code->hasUsesRemaining()) {
                    throw new \Exception('Ce code de compensation a déjà été utilisé.');
                }

                // Get base price from reservation
                $basePrice = (float) $reservation->prix_total;

                // Calculate discount using code's method
                $discountAmount = $code->calculateDiscount($basePrice);

                // Calculate final price (never negative)
                $finalPrice = max(0, $basePrice - $discountAmount);

                // Update reservation with discount info
                $reservation->update([
                    'compensation_code_id' => $code->id,
                    'base_price' => $basePrice,
                    'discount_amount' => $discountAmount,
                    'prix_total' => $finalPrice,
                ]);

                // Increment code usage counter
                $code->increment('uses_count');

                // Update last_used_at timestamp
                $code->update(['last_used_at' => now()]);

                return $reservation;
            },
            attempts: 3
        );
    }

    /**
     * Remove compensation code from a reservation (if possible).
     *
     * @param Reservation $reservation The reservation to remove code from
     * @return Reservation Updated reservation without compensation code
     * @throws \Exception If operation fails
     */
    public function remove(Reservation $reservation): Reservation
    {
        return DB::transaction(
            function () use ($reservation) {
                if (!$reservation->compensation_code_id) {
                    return $reservation; // No code to remove
                }

                $basePrice = $reservation->base_price ?? $reservation->prix_total;

                // Restore base price as final price
                $reservation->update([
                    'compensation_code_id' => null,
                    'base_price' => null,
                    'discount_amount' => 0,
                    'prix_total' => $basePrice,
                ]);

                // Note: We do NOT decrement uses_count because the code was already consumed.
                // The contract is "code can be used X times", and we only decrement on rollback, not removal.

                return $reservation;
            },
            attempts: 3
        );
    }
}
