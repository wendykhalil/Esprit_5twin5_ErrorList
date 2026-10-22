<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * EquipmentAvailabilityService
 *
 * Provides a single reusable method to determine blocked periods for equipment.
 * Uses half-open interval convention: [start_date, end_date)
 *
 * Blocking reservations:
 * - Statuses: en_attente, confirmee, en_cours, litige (pending, confirmed, in progress, dispute)
 * - BUT NOT if the associated delivery has status 'terminee' (equipment returned)
 *
 * Non-blocking reservations:
 * - Statuses: annulea, refusea, or any reservation with status 'terminee'
 * - OR any reservation whose delivery is marked 'terminee'
 *
 * This ensures that once a delivery is marked complete, the original planned dates
 * become available for new reservations, even if the reservation status is still confirmed.
 */
class EquipmentAvailabilityService
{
    /**
     * Get all blocked periods for a given equipment.
     *
     * Blocked periods are determined by reservations that should block dates:
     * - Reservations with blocking statuses (en_attente, confirmee, en_cours, litige)
     *   UNLESS their associated delivery is marked 'terminee' (equipment returned)
     * - Non-blocking statuses (annulea, refusee) never block
     * - Reservations with status 'terminee' never block
     *
     * Uses Reservation::shouldBlockDates() to determine if a reservation blocks,
     * which considers both reservation status AND delivery completion status.
     *
     * @param Equipment $equipment The equipment to check availability for
     * @param Reservation|null $excludeReservation Optional reservation to exclude from results (for editing scenarios)
     * @return Collection<array> Array of blocked periods, each with 'start_date' and 'end_date' keys
     *
     * @example
     *   $service = new EquipmentAvailabilityService();
     *   $blocked = $service->getBlockedPeriods($equipment);
     *   // Returns:
     *   // [
     *   //     ['start_date' => '2027-02-01', 'end_date' => '2027-02-11'],
     *   //     ['start_date' => '2027-02-15', 'end_date' => '2027-02-22'],
     *   // ]
     */
    public function getBlockedPeriods(Equipment $equipment, Reservation $excludeReservation = null): Collection
    {
        // Get potential blocking statuses, but will filter by shouldBlockDates() which checks delivery status
        $blockingStatuses = ['en_attente', 'confirmee', 'en_cours', 'litige'];

        $query = Reservation::where('equipment_id', $equipment->id)
            ->whereIn('statut', $blockingStatuses)
            ->with('delivery')  // Eager load delivery for shouldBlockDates() check
            ->select('id', 'date_debut', 'date_fin', 'statut')
            ->orderBy('date_debut', 'asc');

        // Exclude a specific reservation if provided (for editing scenarios)
        if ($excludeReservation) {
            $query->where('id', '!=', $excludeReservation->id);
        }

        return $query->get()
            ->filter(fn(Reservation $reservation) => $reservation->shouldBlockDates())
            ->map(fn(Reservation $reservation) => [
                'start_date' => $reservation->date_debut->format('Y-m-d'),
                'end_date' => $reservation->date_fin->format('Y-m-d'),
            ]);
    }

    /**
     * Check if a given date range overlaps with any blocking reservations.
     *
     * Uses half-open interval: [start_date, end_date)
     * Overlap exists if: existing.start_date < new.end_date AND existing.end_date > new.start_date
     *
     * Only considers reservations that should actually block dates (via shouldBlockDates()).
     * This excludes reservations whose deliveries are marked 'terminee'.
     *
     * @param Equipment $equipment The equipment to check
     * @param Carbon|string $startDate Start date (inclusive)
     * @param Carbon|string $endDate End date (exclusive)
     * @param Reservation|null $excludeReservation Optional reservation to exclude
     * @return bool True if overlap exists with a blocking reservation, false otherwise
     */
    public function hasOverlap(Equipment $equipment, $startDate, $endDate, Reservation $excludeReservation = null): bool
    {
        $start = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $end = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

        $blockingStatuses = ['en_attente', 'confirmee', 'en_cours', 'litige'];

        $query = Reservation::where('equipment_id', $equipment->id)
            ->whereIn('statut', $blockingStatuses)
            ->with('delivery')  // Eager load delivery for shouldBlockDates() check
            ->whereDate('date_debut', '<', $end)
            ->whereDate('date_fin', '>', $start);

        if ($excludeReservation) {
            $query->where('id', '!=', $excludeReservation->id);
        }

        // Filter by shouldBlockDates() to exclude completed deliveries
        return $query->get()
            ->filter(fn(Reservation $reservation) => $reservation->shouldBlockDates())
            ->count() > 0;
    }

    /**
     * Get the next available date after a given date (for quick lookup).
     *
     * This is a convenience method that finds the first unblocked date
     * after the given date by checking sequential dates.
     *
     * @param Equipment $equipment The equipment to check
     * @param Carbon|string $afterDate Check availability starting after this date
     * @param int $maxDaysToCheck Maximum days to look ahead (default 90)
     * @return Carbon|null The first available date, or null if none found within max days
     */
    public function getNextAvailableDate(Equipment $equipment, $afterDate = null, int $maxDaysToCheck = 90): ?Carbon
    {
        $date = $afterDate instanceof Carbon ? $afterDate : Carbon::parse($afterDate ?? now());

        for ($i = 0; $i < $maxDaysToCheck; $i++) {
            $checkDate = $date->clone()->addDays($i);

            if (!$this->hasOverlap($equipment, $checkDate, $checkDate->clone()->addDay())) {
                return $checkDate;
            }
        }

        return null;
    }
}
