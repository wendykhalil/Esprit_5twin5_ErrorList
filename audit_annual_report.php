#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Equipment;
use App\Models\Incident;
use App\Models\Payment;
use App\Models\Reclamation;
use App\Models\Reservation;
use App\Services\ReportGenerationService;
use Carbon\Carbon;

echo "╔════════════════════════════════════════════════════════════════════════════════╗\n";
echo "║     AUDIT ANNUEL - RAPPORT /admin/reports (01/01/2026 - 20/10/2026)           ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════════╝\n\n";

$startDate = Carbon::parse('2026-01-01');
$endDate = Carbon::parse('2026-10-20');

echo "Période d'audit: {$startDate->format('d/m/Y')} au {$endDate->format('d/m/Y')}\n\n";

$service = app(ReportGenerationService::class);
$stats = $service->aggregateStatistics($startDate, $endDate);

// ============================================================================
// 1. DONNÉES BRUTES - RÉSERVATIONS
// ============================================================================
echo "═" . str_repeat("═", 78) . "═\n";
echo "1. RÉSERVATIONS (Données Brutes)\n";
echo "═" . str_repeat("═", 78) . "═\n\n";

$totalReservations = Reservation::whereBetween('date_debut', [$startDate, $endDate])->count();
$completedReservations = Reservation::whereBetween('date_debut', [$startDate, $endDate])
    ->where('statut', 'terminee')->count();

echo "Total réservations: {$totalReservations}\n";
echo "Réservations terminées: {$completedReservations}\n";

// Réservations par statut
$byStatus = Reservation::whereBetween('date_debut', [$startDate, $endDate])
    ->selectRaw('statut, COUNT(*) as count')
    ->groupBy('statut')
    ->pluck('count', 'statut')
    ->toArray();

echo "\nPar statut:\n";
foreach ($byStatus as $status => $count) {
    echo "  $status: $count\n";
}

// Durée moyenne
$avgDuration = $stats['reservations']['avg_duration_days'];
echo "\nDurée moyenne: " . ($completedReservations > 0 ? "{$avgDuration} jours" : "— (aucune terminée)") . "\n";

// ============================================================================
// 2. DONNÉES BRUTES - REVENUS
// ============================================================================
echo "\n═" . str_repeat("═", 78) . "═\n";
echo "2. REVENUS (Données Brutes de Paiements)\n";
echo "═" . str_repeat("═", 78) . "═\n\n";

$paidTotal = Payment::where('status', 'paid')
    ->whereHas('reservation', fn($q) => $q->whereBetween('date_debut', [$startDate, $endDate]))
    ->sum('amount');

$pendingTotal = Payment::where('status', 'pending')
    ->whereHas('reservation', fn($q) => $q->whereBetween('date_debut', [$startDate, $endDate]))
    ->sum('amount');

$failedTotal = Payment::where('status', 'failed')
    ->whereHas('reservation', fn($q) => $q->whereBetween('date_debut', [$startDate, $endDate]))
    ->sum('amount');

echo "Montants de paiement:\n";
echo "  Paid (collecté): " . number_format($paidTotal, 2, ',', ' ') . " TND\n";
echo "  Pending: " . number_format($pendingTotal, 2, ',', ' ') . " TND\n";
echo "  Failed: " . number_format($failedTotal, 2, ',', ' ') . " TND\n";

$paymentCounts = Payment::whereHas('reservation', fn($q) => $q->whereBetween('date_debut', [$startDate, $endDate]))
    ->selectRaw('status, COUNT(*) as count')
    ->groupBy('status')
    ->pluck('count', 'status')
    ->toArray();

echo "\nNombre de paiements par statut:\n";
foreach ($paymentCounts as $status => $count) {
    echo "  $status: $count\n";
}

// ============================================================================
// 3. DONNÉES BRUTES - REMBOURSEMENTS
// ============================================================================
echo "\n═" . str_repeat("═", 78) . "═\n";
echo "3. REMBOURSEMENTS (Données Brutes de Réclamations)\n";
echo "═" . str_repeat("═", 78) . "═\n\n";

// Remboursements approuvés (exécutés)
$refundedTotal = Reclamation::where('status', 'approuvee')
    ->whereHas('reservation', fn($q) => $q->whereBetween('date_debut', [$startDate, $endDate]))
    ->sum('refund_amount');

echo "Total remboursements (status='approuvee'): " . number_format($refundedTotal, 2, ',', ' ') . " TND\n";

// Détail par statut
$reclamationByStatus = Reclamation::whereHas('reservation', fn($q) => $q->whereBetween('date_debut', [$startDate, $endDate]))
    ->selectRaw('status, COUNT(*) as count, SUM(refund_amount) as total_refund')
    ->groupBy('status')
    ->get();

echo "\nRéclamations par statut:\n";
foreach ($reclamationByStatus as $rec) {
    echo "  {$rec->status}: {$rec->count} réclamations, " . 
         number_format($rec->total_refund ?? 0, 2, ',', ' ') . " TND de remboursement\n";
}

// ============================================================================
// 4. DONNÉES BRUTES - RÉCLAMATIONS vs INCIDENTS
// ============================================================================
echo "\n═" . str_repeat("═", 78) . "═\n";
echo "4. RÉCLAMATIONS vs INCIDENTS (Données Brutes)\n";
echo "═" . str_repeat("═", 78) . "═\n\n";

$totalReclamations = Reclamation::whereHas('reservation', fn($q) => $q->whereBetween('date_debut', [$startDate, $endDate]))
    ->count();

$totalIncidents = Incident::whereHas('reclamation', fn($q) => 
    $q->whereHas('reservation', fn($qq) => $qq->whereBetween('date_debut', [$startDate, $endDate]))
)->count();

echo "Total réclamations: {$totalReclamations}\n";
echo "Total incidents: {$totalIncidents}\n";
echo "Ratio incidents/réclamations: " . ($totalReclamations > 0 ? round($totalIncidents / $totalReclamations, 2) : 0) . "\n";

$incidentsByType = Incident::whereHas('reclamation', fn($q) => 
    $q->whereHas('reservation', fn($qq) => $qq->whereBetween('date_debut', [$startDate, $endDate]))
)->selectRaw('type_probleme, COUNT(*) as count')
    ->groupBy('type_probleme')
    ->pluck('count', 'type_probleme')
    ->toArray();

echo "\nIncidents par type:\n";
foreach ($incidentsByType as $type => $count) {
    echo "  $type: $count\n";
}

// ============================================================================
// 5. DONNÉES BRUTES - REVENUS PAR ÉQUIPEMENT
// ============================================================================
echo "\n═" . str_repeat("═", 78) . "═\n";
echo "5. REVENUS PAR ÉQUIPEMENT (Vérification Double-Comptage)\n";
echo "═" . str_repeat("═", 78) . "═\n\n";

// Requête de vérification: compter les paiements directs
$equipmentRevenue = Payment::where('payments.status', 'paid')
    ->join('reservations', 'payments.reservation_id', '=', 'reservations.id')
    ->join('equipment', 'reservations.equipment_id', '=', 'equipment.id')
    ->whereBetween('reservations.date_debut', [$startDate, $endDate])
    ->selectRaw('equipment.id, equipment.name, COUNT(DISTINCT payments.id) as payment_count, SUM(payments.amount) as total_revenue')
    ->groupBy('equipment.id', 'equipment.name')
    ->orderByDesc('total_revenue')
    ->limit(10)
    ->get();

echo "Top 10 équipements par revenu:\n";
echo sprintf("%-4s %-40s %-15s %-15s\n", "#", "Équipement", "Nb Paiements", "Revenu Total");
echo str_repeat("-", 74) . "\n";

foreach ($equipmentRevenue as $i => $eq) {
    echo sprintf("%-4d %-40s %-15d %-15s\n", 
        $i + 1, 
        substr($eq->name, 0, 40), 
        $eq->payment_count,
        number_format($eq->total_revenue, 2, ',', ' ')
    );
}

// Vérifier double-comptage: comparer avec GROUP BY equipment_id seulement
$simpleTotal = Payment::where('payments.status', 'paid')
    ->join('reservations', 'payments.reservation_id', '=', 'reservations.id')
    ->whereBetween('reservations.date_debut', [$startDate, $endDate])
    ->sum('payments.amount');

echo "\nVérification double-comptage:\n";
echo "  SUM(payments.amount) directement: " . number_format($simpleTotal, 2, ',', ' ') . " TND\n";
echo "  SUM de tous les équipements: " . number_format($equipmentRevenue->sum('total_revenue'), 2, ',', ' ') . " TND\n";
echo "  Cohérence: " . (abs($simpleTotal - $equipmentRevenue->sum('total_revenue')) < 0.01 ? "✓ OK" : "✗ DIVERGENCE") . "\n";

// ============================================================================
// 6. STATISTIQUES SERVICE vs DONNÉES BRUTES
// ============================================================================
echo "\n═" . str_repeat("═", 78) . "═\n";
echo "6. COMPARAISON SERVICE vs DONNÉES BRUTES\n";
echo "═" . str_repeat("═", 78) . "═\n\n";

echo "Réservations:\n";
echo "  Service: {$stats['reservations']['total']} | BD: {$totalReservations} | Match: " . 
    ($stats['reservations']['total'] == $totalReservations ? "✓" : "✗") . "\n";

echo "Revenus collectés:\n";
echo "  Service: " . number_format($stats['revenue']['total'], 2, ',', ' ') . " TND | " .
    "BD (paid): " . number_format($paidTotal, 2, ',', ' ') . " TND | Match: " . 
    (abs($stats['revenue']['total'] - $paidTotal) < 0.01 ? "✓" : "✗") . "\n";

echo "Remboursements:\n";
echo "  Service: " . number_format($stats['revenue']['refunded'], 2, ',', ' ') . " TND | " .
    "BD (approuvee): " . number_format($refundedTotal, 2, ',', ' ') . " TND | Match: " . 
    (abs($stats['revenue']['refunded'] - $refundedTotal) < 0.01 ? "✓" : "✗") . "\n";

echo "Réclamations:\n";
echo "  Service: {$stats['reclamations']['total']} | BD: {$totalReclamations} | Match: " . 
    ($stats['reclamations']['total'] == $totalReclamations ? "✓" : "✗") . "\n";

echo "Incidents:\n";
echo "  Service: {$stats['reclamations']['incidents_total']} | BD: {$totalIncidents} | Match: " . 
    ($stats['reclamations']['incidents_total'] == $totalIncidents ? "✓" : "✗") . "\n";

// ============================================================================
// 7. RÉSUMÉ DÉTERMINISTE vs STATISTIQUES
// ============================================================================
echo "\n═" . str_repeat("═", 78) . "═\n";
echo "7. RÉSUMÉ DÉTERMINISTE vs STATISTIQUES NUMÉRIQUES\n";
echo "═" . str_repeat("═", 78) . "═\n\n";

$summary = $service->generateDeterministicSummary($stats, $startDate, $endDate);

// Extraire les nombres du résumé
echo "Résumé (premiers 500 caractères):\n";
echo "---\n";
echo substr($summary, 0, 500) . "...\n";
echo "---\n\n";

// Vérifier présence des nombres clés
$checks = [
    "Total de réservations: {$totalReservations}" => strpos($summary, (string)$totalReservations) !== false,
    "Revenus totaux: " . number_format($paidTotal, 0) . " TND" => strpos($summary, (string)round($paidTotal)) !== false,
    "Total réclamations: {$totalReclamations}" => strpos($summary, (string)$totalReclamations) !== false,
];

echo "Vérification présence nombres clés dans résumé:\n";
foreach ($checks as $label => $present) {
    echo "  $label: " . ($present ? "✓" : "✗") . "\n";
}

// ============================================================================
// 8. CLASSEMENT ÉQUIPEMENTS - VÉRIFICATION ORDRE
// ============================================================================
echo "\n═" . str_repeat("═", 78) . "═\n";
echo "8. CLASSEMENT ÉQUIPEMENTS (Vérification Ordre)\n";
echo "═" . str_repeat("═", 78) . "═\n\n";

echo "Service - Top 3 par revenu:\n";
foreach (array_slice($stats['equipment_rankings'], 0, 3) as $i => $eq) {
    echo "  " . ($i+1) . ". {$eq['name']} (ID {$eq['id']}): {$eq['revenue']} TND\n";
}

echo "\nService - Top 3 par réservations:\n";
foreach (array_slice($stats['equipment_rankings_by_reservations'], 0, 3) as $i => $eq) {
    echo "  " . ($i+1) . ". {$eq['name']} (ID {$eq['id']}): {$eq['reservation_count']} réservations\n";
}

echo "\n" . str_repeat("═", 80) . "\n";
echo "FIN AUDIT\n";
echo str_repeat("═", 80) . "\n";
