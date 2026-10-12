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
 * Blocking statuses: en_attente, confirmee, en_cours, litige
 * Non-blocking statuses: terminee, annulea, refusee
 */
class EquipmentAvailabilityService
{
    /**
     * Get all blocked periods for a given equipment.
     *
     * Blocked periods are determined by reservations with blocking statuses:
     * - en_attente (pending)
     * - confirmee (confirmed)
     * - en_cours (in progress)
     * - litige (dispute)
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
        $blockingStatuses = ['en_attente', 'confirmee', 'en_cours', 'litige'];

        $query = Reservation::where('equipment_id', $equipment->id)
            ->whereIn('statut', $blockingStatuses)
            ->select('date_debut', 'date_fin')
            ->orderBy('date_debut', 'asc');

        // Exclude a specific reservation if provided (for editing scenarios)
        if ($excludeReservation) {
            $query->where('id', '!=', $excludeReservation->id);
        }

        return $query->get()
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
     * @param Equipment $equipment The equipment to check
     * @param Carbon|string $startDate Start date (inclusive)
     * @param Carbon|string $endDate End date (exclusive)
     * @param Reservation|null $excludeReservation Optional reservation to exclude
     * @return bool True if overlap exists, false otherwise
     */
    public function hasOverlap(Equipment $equipment, $startDate, $endDate, Reservation $excludeReservation = null): bool
    {
        $start = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $end = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

        $blockingStatuses = ['en_attente', 'confirmee', 'en_cours', 'litige'];

        $query = Reservation::where('equipment_id', $equipment->id)
            ->whereIn('statut', $blockingStatuses)
            ->where('date_debut', '<', $end)
            ->where('date_fin', '>', $start);

        if ($excludeReservation) {
            $query->where('id', '!=', $excludeReservation->id);
        }

        return $query->exists();
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
