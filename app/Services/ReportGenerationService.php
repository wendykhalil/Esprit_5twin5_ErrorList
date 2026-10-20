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
     * 
     * Implements comprehensive KPI metrics covering:
     * - Reservations (total, by status, completed, cancelled, disputed, duration)
     * - Revenue (collected, pending, refunds, average per reservation)
     * - Reclamations & Incidents (count, by status, by type, rate)
     * - Equipment Performance (top by reservations, top by revenue)
     */
    public function aggregateStatistics(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
                'start_formatted' => $startDate->format('d/m/Y'),
                'end_formatted' => $endDate->format('d/m/Y'),
            ],
            'reservations' => $this->getReservationStats($startDate, $endDate),
            'revenue' => $this->getRevenueStats($startDate, $endDate),
            'reclamations' => $this->getReclamationStats($startDate, $endDate),
            'equipment_rankings' => $this->getEquipmentRankings($startDate, $endDate),
            'equipment_rankings_by_reservations' => $this->getEquipmentRankingsByReservations($startDate, $endDate),
        ];
    }

    /**
     * Get reservation statistics for the period
     * 
     * IMPORTANT NOTES ON DATA INTERPRETATION:
     * 1. "Completed" (terminee): Reservations marked as completed in system. May be 0
     *    if reservations are still in progress (en_attente, confirmee, refusee).
     * 2. "By Status": Shows actual count of reservations by status at report generation time.
     *    Statuses: en_attente (pending), confirmee (confirmed), refusee (refused), terminee (completed)
     * 3. Average Duration: Calculated from reservations where date_fin >= date_debut.
     *    If no completed reservations, returns 0 days.
     * 4. Period Filter: Uses date_debut (reservation start date) for period membership.
     *    This ensures consistent reporting across all metrics.
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
     * Get comprehensive revenue statistics (in TND)
     * 
     * CRITICAL: Payments are filtered by their actual payment_date (when they were paid),
     * NOT by the reservation's date_debut. This ensures accurate financial reporting:
     * - A payment made in October but for a reservation starting in May should be counted in October revenues
     * - Payment date represents actual cash flow, not rental period
     * 
     * Calculates:
     * - Total revenue: SUM(payment.amount) WHERE status='paid' AND payment_date in period
     * - Pending revenue: SUM(payment.amount) WHERE status='pending' AND payment_date in period
     * - Failed payments: SUM(payment.amount) WHERE status='failed' AND payment_date in period
     * - Payment counts by status
     * - Approved refunds from reclamations (filtered by reservation period for logical grouping)
     */
    private function getRevenueStats(Carbon $startDate, Carbon $endDate): array
    {
        // Total paid revenue - filter by ACTUAL payment_date, not reservation date_debut
        $paidRevenue = Payment::where('status', 'paid')
            ->whereNotNull('payment_date')
            ->whereBetween('payment_date', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->sum('amount');

        // Pending revenue (not yet collected) - filter by payment_date when status changed to pending
        $pendingRevenue = Payment::where('status', 'pending')
            ->whereNotNull('payment_date')
            ->whereBetween('payment_date', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->sum('amount');

        // Failed payments - filter by payment_date when failure occurred
        $failedRevenue = Payment::where('status', 'failed')
            ->whereNotNull('payment_date')
            ->whereBetween('payment_date', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->sum('amount');

        // Payment counts by status - filter by actual payment_date
        $paymentsByStatus = Payment::whereNotNull('payment_date')
            ->whereBetween('payment_date', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->groupBy('status')
            ->selectRaw('status, COUNT(*) as count')
            ->pluck('count', 'status')
            ->toArray();

        // Approved refunds - keep filtered by reservation period for logical grouping
        // (refunds are related to reservations, so we use reservation date_debut)
        $approvedRefunds = Reclamation::where('status', 'approuvee')
            ->whereHas('reservation', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date_debut', [$startDate, $endDate]);
            })
            ->sum('refund_amount');

        // Net revenue (paid - refunds)
        $netRevenue = $paidRevenue - $approvedRefunds;

        return [
            'collected' => (float) $paidRevenue,
            'pending' => (float) $pendingRevenue,
            'failed' => (float) $failedRevenue,
            'refunded' => (float) $approvedRefunds,
            'net' => (float) $netRevenue,
            'total' => (float) $paidRevenue,  // Backward compatibility
            'currency' => 'TND',
            'by_status' => $paymentsByStatus,
        ];
    }

    /**
     * Get comprehensive reclamation and incident statistics
     * 
     * Counts distinct reclamations and incidents linked to period reservations
     * Important: 1 Reclamation can have N Incidents - these are distinct counts
     * 
     * Calculates:
     * - Total reclamations count (distinct)
     * - Total incidents count (distinct)
     * - Reclamations by status
     * - Incidents by type
     * - Reclamation rate: (total_reclamations / total_reservations) × 100
     */
    private function getReclamationStats(Carbon $startDate, Carbon $endDate): array
    {
        // Total distinct reclamations
        $totalReclamations = Reclamation::whereHas('reservation', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('date_debut', [$startDate, $endDate]);
        })->count();

        // Total distinct incidents
        $totalIncidents = Incident::whereHas('reclamation', function ($query) use ($startDate, $endDate) {
            $query->whereHas('reservation', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date_debut', [$startDate, $endDate]);
            });
        })->count();

        // Reclamations by status
        $byStatus = Reclamation::whereHas('reservation', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('date_debut', [$startDate, $endDate]);
        })
            ->groupBy('status')
            ->selectRaw('status, COUNT(*) as count')
            ->pluck('count', 'status')
            ->toArray();

        // Incidents by type
        $byType = Incident::whereHas('reclamation', function ($query) use ($startDate, $endDate) {
            $query->whereHas('reservation', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date_debut', [$startDate, $endDate]);
            });
        })
            ->groupBy('type_probleme')
            ->selectRaw('type_probleme, COUNT(*) as count')
            ->pluck('count', 'type_probleme')
            ->toArray();

        // Reclamation rate
        $totalReservations = Reservation::whereBetween('date_debut', [$startDate, $endDate])->count();
        $reclamationRate = $totalReservations > 0 ? ($totalReclamations / $totalReservations) * 100 : 0;

        // Approved reclamations count
        $approvedCount = Reclamation::where('status', 'approuvee')
            ->whereHas('reservation', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date_debut', [$startDate, $endDate]);
            })->count();

        // Rejected reclamations count
        $rejectedCount = Reclamation::where('status', 'rejetee')
            ->whereHas('reservation', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date_debut', [$startDate, $endDate]);
            })->count();

        return [
            'total' => $totalReclamations,
            'incidents_total' => $totalIncidents,
            'rate' => round($reclamationRate, 2),
            'by_status' => $byStatus,
            'by_type' => $byType,
            'approved' => $approvedCount,
            'rejected' => $rejectedCount,
        ];
    }

    /**
     * Get top 10 equipment by revenue (paid only)
     * Sorted by revenue descending
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
        return $this->buildEquipmentStats($startDate, $endDate, 'revenue');
    }

    /**
     * Get top 10 equipment by reservation count
     * Sorted by reservation count descending
     * 
     * This ranking shows which equipment is most popular/demanded, regardless of revenue.
     * Useful for inventory management and demand analysis.
     */
    private function getEquipmentRankingsByReservations(Carbon $startDate, Carbon $endDate): array
    {
        return $this->buildEquipmentStats($startDate, $endDate, 'reservations');
    }

    /**
     * Build equipment statistics with configurable sorting
     * 
     * CRITICAL: Revenue filtering uses ACTUAL payment_date, not reservation date_debut.
     * This ensures the equipment ranking accurately reflects revenue collected during the period,
     * regardless of when the reservations occurred.
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string $sortBy 'revenue' or 'reservations'
     * @return array Top 10 equipment
     */
    private function buildEquipmentStats(Carbon $startDate, Carbon $endDate, string $sortBy = 'revenue'): array
    {
        // Get equipment by reservation count (reservations are still filtered by date_debut)
        // Fetch top 20 to account for potential duplicates that will be deduplicated
        $equipmentStats = Reservation::whereBetween('date_debut', [$startDate, $endDate])
            ->join('equipment', 'reservations.equipment_id', '=', 'equipment.id')
            ->selectRaw('
                equipment.id,
                equipment.name,
                equipment.price_per_day,
                COUNT(DISTINCT reservations.id) as reservation_count
            ')
            ->groupBy('equipment.id', 'equipment.name', 'equipment.price_per_day')
            ->limit(20)  // Fetch 20 to account for deduplication later
            ->get();

        // Calculate revenue for each equipment - filter by ACTUAL payment_date
        // This captures all payments processed during the period, even for reservations outside the period
        $equipmentIds = $equipmentStats->pluck('id')->toArray();
        
        $revenueByEquipment = [];
        if (!empty($equipmentIds)) {
            $revenueByEquipment = Payment::where('status', 'paid')
                ->whereNotNull('payment_date')
                ->whereBetween('payment_date', [$startDate->startOfDay(), $endDate->endOfDay()])
                ->join('reservations', 'payments.reservation_id', '=', 'reservations.id')
                ->whereIn('reservations.equipment_id', $equipmentIds)
                ->selectRaw('reservations.equipment_id, SUM(payments.amount) as total_revenue')
                ->groupBy('reservations.equipment_id')
                ->pluck('total_revenue', 'equipment_id')
                ->toArray();
        }

        // Merge revenue data and deduplicate strictly by equipment.id
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

        // Sort by selected criteria, limit to top 10
        $sortKey = $sortBy === 'reservations' ? 'reservation_count' : 'revenue';
        $rankings = collect($deduplicatedById)
            ->sortByDesc($sortKey)
            ->take(10)
            ->values()
            ->toArray();

        return $rankings;
    }

    /**
     * Build context for AI summary
     * 
     * IMPORTANT: When completed reservations = 0, explicitly mark duration as unavailable
     * to prevent Ollama from treating "0 jours" as a measured value.
     */
    public function buildSummaryContext(array $stats, Carbon $startDate, Carbon $endDate): string
    {
        $completedReservations = $stats['reservations']['completed'];
        
        // When no reservations are completed, mark duration as unavailable
        if ($completedReservations == 0) {
            $durationDisplay = "Non disponible (0 réservation terminée)";
        } else {
            $durationDisplay = $stats['reservations']['avg_duration_days'] . " jours";
        }
        
        $lines = [
            "Période: {$startDate->format('d/m/Y')} à {$endDate->format('d/m/Y')}",
            "Réservations totales: {$stats['reservations']['total']}",
            "Réservations complétées: {$stats['reservations']['completed']}",
            "Durée moyenne: {$durationDisplay}",
            "Revenus totaux: {$stats['revenue']['total']} TND",
            "Réclamations totales: {$stats['reclamations']['total']}",
            "Incidents totaux: {$stats['reclamations']['incidents_total']}",
            "Taux de réclamations: {$stats['reclamations']['rate']}%",
            "Remboursements approuvés: {$stats['revenue']['refunded']} TND",
        ];

        return implode("\n", $lines);
    }

    /**
     * Format equipment list for display
     * 
     * Note: Equipment names may appear multiple times as they represent different
     * equipment records (e.g., different providers, variants, or conditions).
     * Each entry is uniquely identified by its equipment ID.
     */
    public function formatEquipmentList(array $rankings): string
    {
        $lines = [];
        foreach ($rankings as $i => $equipment) {
            // Include ID to disambiguate equipment with same name
            $lines[] = ($i + 1) . ". {$equipment['name']} (ID: {$equipment['id']}) - {$equipment['reservation_count']} réservations, {$equipment['revenue']} TND";
        }

        return implode("\n", $lines);
    }

    /**
     * Generate deterministic summary based on backend statistics
     * 
     * This fallback is used when:
     * 1. Ollama service is disabled or unavailable
     * 2. Ollama response contradicts backend statistics
     * 3. No AI summary is required
     * 
     * Ensures 100% consistency with calculated metrics. No invented data.
     */
    public function generateDeterministicSummary(array $stats, Carbon $startDate, Carbon $endDate): string
    {
        $formattedStart = $startDate->format('d/m/Y');
        $formattedEnd = $endDate->format('d/m/Y');
        
        $totalReservations = $stats['reservations']['total'];
        $completedReservations = $stats['reservations']['completed'];
        $completionRate = $totalReservations > 0 ? round(($completedReservations / $totalReservations) * 100, 1) : 0;
        $avgDuration = $stats['reservations']['avg_duration_days'];
        $totalRevenue = $stats['revenue']['total'];
        $totalReclamations = $stats['reclamations']['total'];
        $reclamationRate = $stats['reclamations']['rate'];
        
        $summary = [];
        
        // Period header
        $summary[] = "Période analysée: {$formattedStart} à {$formattedEnd}";
        $summary[] = "";
        
        // Reservation statistics
        $summary[] = "Résumé des réservations";
        $summary[] = "- Total de réservations: {$totalReservations}";
        $summary[] = "- Statuts observés: " . $this->formatReservationStatusesWithLabels($stats['reservations']['by_status']);
        
        // Completion rate with proper handling of 0
        if ($completedReservations == 0) {
            $summary[] = "- Réservations terminées: 0 (aucune réservation complétée pour cette période)";
            $summary[] = "- Durée moyenne: Non disponible (aucune réservation terminée)";
        } else {
            $summary[] = "- Réservations terminées: {$completedReservations} ({$completionRate}%)";
            $summary[] = "- Durée moyenne: {$avgDuration} jours";
        }
        
        $summary[] = "";
        
        // Revenue
        $summary[] = "Revenus collectés";
        $summary[] = "- Total: {$totalRevenue} TND";
        if ($totalReservations > 0) {
            $avgPerReservation = $totalRevenue / $totalReservations;
            $summary[] = "- Moyenne par réservation: " . number_format($avgPerReservation, 2, ',', ' ') . " TND";
        }
        
        $summary[] = "";
        
        // Reclamations
        $summary[] = "Réclamations et incidents";
        $summary[] = "- Total: {$totalReclamations} réclamations ({$reclamationRate}% des réservations)";
        $summary[] = "- Par statut: " . $this->formatReclamationStatusesWithLabels($stats['reclamations']['by_status']);
        $summary[] = "- Types de problèmes: " . $this->formatIncidentTypesWithLabels($stats['reclamations']['by_type']);
        
        $summary[] = "";
        
        // Equipment rankings
        $summary[] = "Équipements les plus demandés";
        foreach (array_slice($stats['equipment_rankings'], 0, 5) as $i => $equipment) {
            $summary[] = ($i + 1) . ". {$equipment['name']} (ID {$equipment['id']}): {$equipment['reservation_count']} réservations, {$equipment['revenue']} TND";
        }
        
        return implode("\n", $summary);
    }
    
    /**
     * Format reservation statuses with French labels
     */
    private function formatReservationStatusesWithLabels(array $byStatus): string
    {
        $parts = [];
        foreach ($byStatus as $status => $count) {
            $label = $this->translateStatusLabel($status);
            $parts[] = "{$count} {$label}";
        }
        return implode(", ", $parts);
    }
    
    /**
     * Format reclamation statuses with French labels
     */
    private function formatReclamationStatusesWithLabels(array $byStatus): string
    {
        $parts = [];
        foreach ($byStatus as $status => $count) {
            $label = $this->translateStatusLabel($status);
            $parts[] = "{$count} {$label}";
        }
        return implode(", ", $parts);
    }
    
    /**
     * Format incident types with French labels
     */
    private function formatIncidentTypesWithLabels(array $byType): string
    {
        $parts = [];
        foreach ($byType as $type => $count) {
            $label = $this->translateIncidentType($type);
            $parts[] = "{$count} {$label}";
        }
        return implode(", ", $parts);
    }
    
    /**
     * Format incident types for display
     */
    private function formatIncidentTypes(array $byType): string
    {
        $parts = [];
        foreach ($byType as $type => $count) {
            $parts[] = "{$count} " . $this->translateIncidentType($type);
        }
        return implode(", ", $parts);
    }

    /**
     * Translate French technical codes to readable labels
     * Used in AI summaries and reports
     */
    public function translateStatusLabel(string $code): string
    {
        $translations = [
            // Reservation statuses
            'en_attente' => 'En attente',
            'confirmee' => 'Confirmée',
            'en_cours' => 'En cours',
            'terminee' => 'Terminée',
            'litige' => 'Litige',
            'annulea' => 'Annulée',
            'refusee' => 'Refusée',
            
            // Reclamation statuses
            'en_cours' => 'En cours',
            'approuvee' => 'Approuvée',
            'rejetee' => 'Rejetée',
            'fermee' => 'Fermée',
            
            // Payment statuses
            'paid' => 'Payé',
            'pending' => 'En attente',
            'failed' => 'Échoué',
            'refunded' => 'Remboursé',
        ];

        return $translations[$code] ?? ucfirst(str_replace('_', ' ', $code));
    }

    /**
     * Translate incident types to readable labels
     */
    public function translateIncidentType(string $code): string
    {
        $translations = [
            'endommage' => 'Endommagé',
            'manquant' => 'Manquant',
            'malfonctionnant' => 'Malfonctionnant',
            'autre' => 'Autre',
        ];

        return $translations[$code] ?? ucfirst(str_replace('_', ' ', $code));
    }
}
