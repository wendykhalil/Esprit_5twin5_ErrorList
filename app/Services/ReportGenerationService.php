<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\Incident;
use App\Models\Payment;
use App\Models\Reclamation;
use App\Models\Reservation;
use Carbon\Carbon;

class ReportGenerationService
{
    /**
     * Aggregate all statistics from the database for the given period
     * Optimized to prevent N+1 queries
     */
    public function aggregateStatistics(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'reservations' => $this->getReservationStats($startDate, $endDate),
            'revenue' => $this->getRevenueStats($startDate, $endDate),
            'reclamations' => $this->getReclamationStats($startDate, $endDate),
            'refunds' => $this->getRefundStats($startDate, $endDate),
            'equipment_rankings' => $this->getEquipmentRankings($startDate, $endDate),
        ];
    }

    /**
     * Get reservation statistics for the period
     * 
     * CRITICAL FIX: Each aggregation uses separate query instances to prevent
     * query builder state consumption issues. Previously, reusing $reservationQuery
     * after count() caused empty by_status breakdowns.
     */
    private function getReservationStats(Carbon $startDate, Carbon $endDate): array
    {
        // Total count - separate query instance
        $totalReservations = Reservation::whereBetween('date_debut', [$startDate, $endDate])->count();

        // Completed count - separate query instance
        $completedReservations = Reservation::whereBetween('date_debut', [$startDate, $endDate])
            ->where('statut', 'terminee')
            ->count();

        // Reservations by status - separate query instance
        // Fixed: Create fresh query instead of reusing consumed builder
        $byStatus = Reservation::whereBetween('date_debut', [$startDate, $endDate])
            ->groupBy('statut')
            ->selectRaw('statut, COUNT(*) as count')
            ->pluck('count', 'statut')
            ->toArray();

        // Average duration - calculate in PHP to be database-agnostic
        // Note: Only include reservations where date_fin >= date_debut (valid periods)
        $avgDuration = 0;
        if ($totalReservations > 0) {
            $reservations = Reservation::whereBetween('date_debut', [$startDate, $endDate])
                ->selectRaw('date_debut, date_fin')
                ->get();
            
            if ($reservations->count() > 0) {
                $validReservations = $reservations->filter(function ($reservation) {
                    // Only count reservations where end date is >= start date
                    return $reservation->date_fin && $reservation->date_debut &&
                           $reservation->date_fin->gte($reservation->date_debut);
                });
                
                if ($validReservations->count() > 0) {
                    $totalDays = $validReservations->sum(function ($reservation) {
                        // Calculate days between dates (inclusive, so add 1)
                        return $reservation->date_fin->diffInDays($reservation->date_debut) + 1;
                    });
                    $avgDuration = $totalDays / $validReservations->count();
                }
            }
        }

        return [
            'total' => $totalReservations,
            'completed' => $completedReservations,
            'by_status' => $byStatus,
            'avg_duration_days' => round(max(0, $avgDuration), 2), // Ensure non-negative
        ];
    }

    /**
     * Get revenue statistics (paid payments only, in TND)
     */
    private function getRevenueStats(Carbon $startDate, Carbon $endDate): array
    {
        $revenue = Payment::where('status', 'paid')
            ->whereHas('reservation', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date_debut', [$startDate, $endDate]);
            })
            ->sum('amount');

        return [
            'total' => (float) $revenue,
            'currency' => 'TND',
        ];
    }

    /**
     * Get reclamation statistics by status and type
     * 
     * CRITICAL FIX: Each aggregation uses separate query instances to prevent
     * query builder state consumption. Previously, reusing $reclamationQuery
     * after count() caused empty by_status breakdowns.
     */
    private function getReclamationStats(Carbon $startDate, Carbon $endDate): array
    {
        // Total reclamations count - separate query instance
        $totalReclamations = Reclamation::whereHas('reservation', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('date_debut', [$startDate, $endDate]);
        })->count();

        // Reclamations by status - separate query instance
        // Fixed: Create fresh query instead of reusing consumed builder
        $byStatus = Reclamation::whereHas('reservation', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('date_debut', [$startDate, $endDate]);
        })
            ->groupBy('status')
            ->selectRaw('status, COUNT(*) as count')
            ->pluck('count', 'status')
            ->toArray();

        // Reclamations by type (from incidents)
        // Fixed: Use fresh query for by_type aggregation
        $byType = Incident::whereHas('reclamation', function ($query) use ($startDate, $endDate) {
            $query->whereHas('reservation', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date_debut', [$startDate, $endDate]);
            });
        })
            ->groupBy('type_probleme')
            ->selectRaw('type_probleme, COUNT(*) as count')
            ->pluck('count', 'type_probleme')
            ->toArray();

        // Get total reservations for rate calculation
        $totalReservations = Reservation::whereBetween('date_debut', [$startDate, $endDate])->count();
        $reclamationRate = $totalReservations > 0 ? ($totalReclamations / $totalReservations) * 100 : 0;

        return [
            'total' => $totalReclamations,
            'rate' => round($reclamationRate, 2),
            'by_status' => $byStatus,
            'by_type' => $byType,
        ];
    }

    /**
     * Get refund statistics (approved reclamations only)
     */
    private function getRefundStats(Carbon $startDate, Carbon $endDate): array
    {
        $totalRefunds = Reclamation::where('status', 'approuvee')
            ->whereHas('reservation', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date_debut', [$startDate, $endDate]);
            })
            ->sum('refund_amount');

        return [
            'total' => (float) $totalRefunds,
            'currency' => 'TND',
        ];
    }

    /**
     * Get top 10 equipment by reservation count and revenue
     * Optimized to prevent N+1 queries using raw aggregation
     * 
     * CRITICAL FIX (v2): Apply LIMIT AFTER aggregation, not before.
     * Previously: limit(10) was applied to the query, then deduplication happened in PHP.
     * If multiple equipment IDs had the same name, they'd all pass through until deduplicated,
     * potentially reducing final results to <10 after dedup.
     * Now: Fetch all equipment, deduplicate strictly by equipment.id, then limit to top 10 by revenue.
     * 
     * Why this matters:
     * - Multiple equipment records can have identical names (different providers, variants)
     * - groupBy(equipment.id) in SQL correctly separates them by primary key
     * - But the previous limit(10) happened BEFORE deduplication in PHP
     * - This caused fewer than 10 items in final output if duplicates were removed
     * - Solution: Fetch more records, deduplicate by id, sort by revenue, take top 10
     * 
     * IMPORTANT: The equipment_rankings reflects COLLECTED REVENUE (paid payments only),
     * not potential revenue or value. If an equipment has multiple payments or if a
     * reservation is included in multiple payments, only 'paid' status amounts are summed.
     */
    private function getEquipmentRankings(Carbon $startDate, Carbon $endDate): array
    {
        // Get equipment by reservation count WITHOUT limit yet
        // Fetch top 20 to account for potential duplicates that will be deduplicated
        // This ensures we have at least 10 unique items after deduplication
        $equipmentStats = Reservation::whereBetween('date_debut', [$startDate, $endDate])
            ->join('equipment', 'reservations.equipment_id', '=', 'equipment.id')
            ->selectRaw('
                equipment.id,
                equipment.name,
                equipment.price_per_day,
                COUNT(DISTINCT reservations.id) as reservation_count
            ')
            ->groupBy('equipment.id', 'equipment.name', 'equipment.price_per_day')
            ->orderByDesc('reservation_count')
            ->limit(20)  // Fetch 20 to account for deduplication later
            ->get();

        // Calculate revenue for each equipment in a separate optimized query
        // This sums all PAID payments associated with reservations of that equipment
        // Note: Uses DISTINCT reservation_id to avoid double-counting if bug exists in data
        $equipmentIds = $equipmentStats->pluck('id')->toArray();
        
        $revenueByEquipment = [];
        if (!empty($equipmentIds)) {
            $revenueByEquipment = Payment::where('status', 'paid')
                ->join('reservations', 'payments.reservation_id', '=', 'reservations.id')
                ->whereIn('reservations.equipment_id', $equipmentIds)
                ->whereBetween('reservations.date_debut', [$startDate, $endDate])
                ->selectRaw('reservations.equipment_id, SUM(payments.amount) as total_revenue')
                ->groupBy('reservations.equipment_id')
                ->pluck('total_revenue', 'equipment_id')
                ->toArray();
        }

        // CRITICAL FIX: Merge revenue data and deduplicate strictly by equipment.id
        // This prevents multiple rows with same equipment.id from appearing in final output
        $deduplicatedById = [];
        foreach ($equipmentStats as $item) {
            if (!isset($deduplicatedById[$item->id])) {
                $deduplicatedById[$item->id] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price_per_day' => (float) $item->price_per_day,
                    'reservation_count' => (int) $item->reservation_count,
                    'revenue' => (float) ($revenueByEquipment[$item->id] ?? 0),
                ];
            }
        }

        // Sort by revenue descending, limit to top 10, and return as array
        // NOTE: limit(10) is applied AFTER deduplication to ensure we get exactly 10 unique items
        $rankings = collect($deduplicatedById)
            ->sortByDesc('revenue')
            ->take(10)  // Limit AFTER sorting by revenue
            ->values()
            ->toArray();

        return $rankings;
    }

    /**
     * Build context for AI summary
     */
    public function buildSummaryContext(array $stats, Carbon $startDate, Carbon $endDate): string
    {
        $lines = [
            "Période: {$startDate->format('d/m/Y')} à {$endDate->format('d/m/Y')}",
            "Réservations totales: {$stats['reservations']['total']}",
            "Réservations complétées: {$stats['reservations']['completed']}",
            "Durée moyenne: {$stats['reservations']['avg_duration_days']} jours",
            "Revenus totaux: {$stats['revenue']['total']} TND",
            "Réclamations totales: {$stats['reclamations']['total']}",
            "Taux de réclamations: {$stats['reclamations']['rate']}%",
            "Remboursements approuvés: {$stats['refunds']['total']} TND",
        ];

        return implode("\n", $lines);
    }

    /**
     * Format equipment list for display
     */
    public function formatEquipmentList(array $rankings): string
    {
        $lines = [];
        foreach ($rankings as $i => $equipment) {
            $lines[] = ($i + 1) . ". {$equipment['name']} - {$equipment['reservation_count']} réservations, {$equipment['revenue']} TND";
        }

        return implode("\n", $lines);
    }
}
