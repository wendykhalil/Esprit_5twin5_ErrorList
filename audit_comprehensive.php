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

$startDate = Carbon::parse('2026-01-01');
$endDate = Carbon::parse('2026-10-20');

$service = app(ReportGenerationService::class);
$stats = $service->aggregateStatistics($startDate, $endDate);

$checks = [];

// ============================================================================
// 2. COHÉRENCE RÉSUMÉ IA vs TABLEAUX
// ============================================================================
echo "╔════════════════════════════════════════════════════════════════════════╗\n";
echo "║ 2. COHÉRENCE RÉSUMÉ IA vs STATISTIQUES NUMÉRIQUES                      ║\n";
echo "╚════════════════════════════════════════════════════════════════════════╝\n\n";

$summary = $service->generateDeterministicSummary($stats, $startDate, $endDate);

// Vérifier présence des nombres exacts
$reservationMatch = strpos($summary, "Total de réservations: {$stats['reservations']['total']}") !== false;
$revenueMatch = strpos($summary, (string)round($stats['revenue']['total'])) !== false;
$reclamationMatch = strpos($summary, "Total: {$stats['reclamations']['total']}") !== false;

echo "Vérifications de cohérence:\n";
echo "  Réservations ({$stats['reservations']['total']}): " . ($reservationMatch ? "✓" : "✗") . "\n";
echo "  Revenus (~" . round($stats['revenue']['total']) . " TND): " . ($revenueMatch ? "✓" : "✗") . "\n";
echo "  Réclamations ({$stats['reclamations']['total']}): " . ($reclamationMatch ? "✓" : "✗") . "\n";

$checks['ia_summary_coherence'] = $reservationMatch && $revenueMatch && $reclamationMatch;

// ============================================================================
// 3. ÉQUIPEMENTS - COHÉRENCE CLASSEMENTS
// ============================================================================
echo "\n╔════════════════════════════════════════════════════════════════════════╗\n";
echo "║ 3. ÉQUIPEMENTS - VÉRIFICATION COHÉRENCE CLASSEMENTS                    ║\n";
echo "╚════════════════════════════════════════════════════════════════════════╝\n\n";

// Top 3 par revenu vs top 3 dans le résumé
echo "Top 3 par revenu (classement_revenues):\n";
$topByRevenue = array_slice($stats['equipment_rankings'], 0, 3);
foreach ($topByRevenue as $i => $eq) {
    echo "  " . ($i+1) . ". ID {$eq['id']} ({$eq['name']}): {$eq['revenue']} TND\n";
}

echo "\nTop 3 par réservations (classement_demande):\n";
$topByReservations = array_slice($stats['equipment_rankings_by_reservations'], 0, 3);
foreach ($topByReservations as $i => $eq) {
    echo "  " . ($i+1) . ". ID {$eq['id']} ({$eq['name']}): {$eq['reservation_count']} réservations\n";
}

// Vérifier que les deux classements existent et ont du contenu
$revenueExists = count($stats['equipment_rankings']) > 0;
$reservationsExists = count($stats['equipment_rankings_by_reservations']) > 0;
$divergent = !empty($topByRevenue) && !empty($topByReservations) && 
             ($topByRevenue[0]['id'] !== $topByReservations[0]['id']);

echo "\nVérifications:\n";
echo "  Classement par revenu existe: " . ($revenueExists ? "✓" : "✗") . "\n";
echo "  Classement par réservations existe: " . ($reservationsExists ? "✓" : "✗") . "\n";
echo "  Classements divergent (normal): " . ($divergent ? "✓" : "✗") . "\n";

$checks['equipment_rankings_coherence'] = $revenueExists && $reservationsExists;

// ============================================================================
// 4. REVENUS PAR ÉQUIPEMENT - VÉRIFICATION JOINTURES
// ============================================================================
echo "\n╔════════════════════════════════════════════════════════════════════════╗\n";
echo "║ 4. REVENUS PAR ÉQUIPEMENT - VÉRIFICATION DOUBLE-COMPTAGE               ║\n";
echo "╚════════════════════════════════════════════════════════════════════════╝\n\n";

// Requête directe: SUM(payments) sans jointure
$directSum = Payment::where('payments.status', 'paid')
    ->join('reservations', 'payments.reservation_id', '=', 'reservations.id')
    ->whereBetween('reservations.date_debut', [$startDate, $endDate])
    ->sum('payments.amount');

// Vérification: somme des revenus de tous les équipements
$equipmentSum = array_sum(array_map(fn($e) => $e['revenue'], $stats['equipment_rankings']));

echo "Vérification somme revenus:\n";
echo "  SUM direct (payments): " . number_format($directSum, 2, ',', ' ') . " TND\n";
echo "  SUM all equipment_rankings: " . number_format($equipmentSum, 2, ',', ' ') . " TND\n";
echo "  Service revenue.total: " . number_format($stats['revenue']['total'], 2, ',', ' ') . " TND\n";

$directMatch = abs($directSum - $stats['revenue']['total']) < 0.01;
$equipmentMatch = abs($equipmentSum - $stats['revenue']['total']) < 0.01;

echo "  Direct SUM = Service: " . ($directMatch ? "✓" : "✗") . "\n";
echo "  Equipment SUM = Service: " . ($equipmentMatch ? "✓" : "✗") . "\n";

$checks['revenue_no_duplication'] = $directMatch && $equipmentMatch;

// ============================================================================
// 5. RÉCLAMATIONS vs INCIDENTS
// ============================================================================
echo "\n╔════════════════════════════════════════════════════════════════════════╗\n";
echo "║ 5. RÉCLAMATIONS vs INCIDENTS - DISTINCTION CLAIRE                      ║\n";
echo "╚════════════════════════════════════════════════════════════════════════╝\n\n";

echo "Données brutes:\n";
echo "  Réclamations (total): {$stats['reclamations']['total']}\n";
echo "  Incidents (total): {$stats['reclamations']['incidents_total']}\n";
echo "  Ratio: " . ($stats['reclamations']['total'] > 0 ? 
    round($stats['reclamations']['incidents_total'] / $stats['reclamations']['total'], 2) : 0) . 
    " incidents/réclamation\n";

echo "\nRéclamations par statut:\n";
foreach ($stats['reclamations']['by_status'] as $status => $count) {
    echo "  $status: $count\n";
}

echo "\nIncidents par type:\n";
foreach ($stats['reclamations']['by_type'] as $type => $count) {
    echo "  $type: $count\n";
}

$reclamationExists = $stats['reclamations']['total'] > 0;
$incidentExists = $stats['reclamations']['incidents_total'] > 0;
$distincit = $stats['reclamations']['total'] != $stats['reclamations']['incidents_total'];

echo "\nVérifications:\n";
echo "  Réclamations comptabilisées: " . ($reclamationExists ? "✓" : "✗") . "\n";
echo "  Incidents comptabilisés: " . ($incidentExists ? "✓" : "✗") . "\n";
echo "  Distinction claire (réclamations ≠ incidents): " . ($distincit ? "✓" : "✗") . "\n";

$checks['reclamations_incidents_distinction'] = $reclamationExists && $incidentExists && $distincit;

// ============================================================================
// 6. REMBOURSEMENTS - VÉRIFICATION SOURCE
// ============================================================================
echo "\n╔════════════════════════════════════════════════════════════════════════╗\n";
echo "║ 6. REMBOURSEMENTS - VÉRIFICATION SOURCE DE DONNÉES                     ║\n";
echo "╚════════════════════════════════════════════════════════════════════════╝\n\n";

// Remboursements approuvés (exécutés)
$refundApproved = Reclamation::where('status', 'approuvee')
    ->whereHas('reservation', fn($q) => $q->whereBetween('date_debut', [$startDate, $endDate]))
    ->sum('refund_amount');

// Remboursements totaux (peu importe le statut)
$refundAll = Reclamation::whereHas('reservation', fn($q) => $q->whereBetween('date_debut', [$startDate, $endDate]))
    ->sum('refund_amount');

echo "Données brutes:\n";
echo "  Remboursements (approuvee): " . number_format($refundApproved, 2, ',', ' ') . " TND\n";
echo "  Remboursements (all status): " . number_format($refundAll, 2, ',', ' ') . " TND\n";
echo "  Service revenue.refunded: " . number_format($stats['revenue']['refunded'], 2, ',', ' ') . " TND\n";

$refundMatch = abs($stats['revenue']['refunded'] - $refundApproved) < 0.01;

echo "\nVérifications:\n";
echo "  Service utilise 'approuvee' uniquement: " . ($refundMatch ? "✓" : "✗") . "\n";

$checks['refunds_from_approved'] = $refundMatch;

// ============================================================================
// 7. DURÉE MOYENNE
// ============================================================================
echo "\n╔════════════════════════════════════════════════════════════════════════╗\n";
echo "║ 7. DURÉE MOYENNE - AFFICHAGE CORRECT                                   ║\n";
echo "╚════════════════════════════════════════════════════════════════════════╝\n\n";

$completedCount = Reservation::whereBetween('date_debut', [$startDate, $endDate])
    ->where('statut', 'terminee')->count();

echo "Données brutes:\n";
echo "  Réservations terminées: {$completedCount}\n";
echo "  Service avg_duration_days: {$stats['reservations']['avg_duration_days']}\n";

// Vérifier affichage dans résumé
$durationDisplay = $completedCount == 0 ? "Non disponible" : "jours";
$durationInSummary = strpos($summary, $durationDisplay) !== false;

echo "\nVérifications:\n";
echo "  Durée = « Non disponible » quand 0 terminées: " . ($durationInSummary ? "✓" : "✗") . "\n";

$checks['duration_display_correct'] = $durationInSummary;

// ============================================================================
// RÉSUMÉ DES VÉRIFICATIONS
// ============================================================================
echo "\n╔════════════════════════════════════════════════════════════════════════╗\n";
echo "║ RÉSUMÉ DES VÉRIFICATIONS                                               ║\n";
echo "╚════════════════════════════════════════════════════════════════════════╝\n\n";

$allPass = true;
foreach ($checks as $name => $result) {
    $status = $result ? "✓" : "✗";
    echo "$status $name\n";
    $allPass = $allPass && $result;
}

echo "\n" . str_repeat("═", 72) . "\n";
if ($allPass) {
    echo "✓ TOUS LES AUDITS SONT PASSANTS\n";
} else {
    echo "✗ CERTAINS AUDITS ONT ÉCHOUÉ\n";
}
echo str_repeat("═", 72) . "\n";
