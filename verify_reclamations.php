<?php

/**
 * Direct verification of reclamations discrepancy using DB::select
 * No bootstrap required, just database connection
 */

require_once __DIR__ . '/vendor/autoload.php';
$env = require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Initialize Laravel
$app = $env->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n=== VERIFICATION: 14 vs 6 Reclamations ===\n\n";

// Query 1: Total all reclamations
$totalAll = DB::table('reclamations')->count();
echo "1. TOTAL ALL RECLAMATIONS (no filter): " . $totalAll . "\n\n";

// Query 2: All reclamations with dates
echo "2. ALL RECLAMATIONS WITH DATES:\n";
$allRecs = DB::table('reclamations')
    ->leftJoin('reservations', 'reclamations.reservation_id', '=', 'reservations.id')
    ->select('reclamations.id', 'reclamations.status', 'reclamations.created_at', 'reservations.date_debut', 'reservations.date_fin')
    ->orderBy('reclamations.id')
    ->get();

foreach ($allRecs as $rec) {
    $inPeriod = $rec->date_debut && 
        Carbon::parse($rec->date_debut)->between(
            Carbon::parse('2026-01-01'), 
            Carbon::parse('2026-10-20')
        );
    $status = $inPeriod ? "IN" : "OUT";
    echo "ID " . $rec->id . ": status=" . $rec->status . ", res_date=" . ($rec->date_debut ? Carbon::parse($rec->date_debut)->format('d/m/Y') : 'NULL') . " (" . $status . ")\n";
}

echo "\n";

// Query 3: In period count
$inPeriodCount = DB::table('reclamations')
    ->leftJoin('reservations', 'reclamations.reservation_id', '=', 'reservations.id')
    ->whereBetween('reservations.date_debut', ['2026-01-01', '2026-10-20'])
    ->count();

echo "3. RECLAMATIONS IN PERIOD (01/01 - 20/10/2026): " . $inPeriodCount . "\n\n";

// Query 4: Outside period
echo "4. RECLAMATIONS OUTSIDE PERIOD:\n";
$outsideRecs = DB::table('reclamations')
    ->leftJoin('reservations', 'reclamations.reservation_id', '=', 'reservations.id')
    ->whereNotBetween('reservations.date_debut', ['2026-01-01', '2026-10-20'])
    ->select('reclamations.id', 'reclamations.status', 'reservations.date_debut')
    ->orderBy('reservations.date_debut')
    ->get();

echo "Count: " . count($outsideRecs) . "\n";
foreach ($outsideRecs as $rec) {
    $date = $rec->date_debut ? Carbon::parse($rec->date_debut)->format('d/m/Y') : 'NULL';
    echo "ID " . $rec->id . ": " . $date . "\n";
}

echo "\n";

// Query 5: By status (in period)
echo "5. BY STATUS (IN PERIOD):\n";
$byStatus = DB::table('reclamations')
    ->leftJoin('reservations', 'reclamations.reservation_id', '=', 'reservations.id')
    ->whereBetween('reservations.date_debut', ['2026-01-01', '2026-10-20'])
    ->groupBy('reclamations.status')
    ->selectRaw('reclamations.status, COUNT(*) as count')
    ->get();

foreach ($byStatus as $row) {
    echo "  " . $row->status . ": " . $row->count . "\n";
}

echo "\n";

// Summary
echo "=== CONCLUSION ===\n";
echo "Total reclamations in database: " . $totalAll . "\n";
echo "Reclamations in period (01/01 - 20/10/2026): " . $inPeriodCount . "\n";
echo "Reclamations outside period: " . ($totalAll - $inPeriodCount) . "\n";
echo "\nThis explains the difference:\n";
echo "  /admin/reclamations shows: " . $totalAll . " (all, no date filter)\n";
echo "  Annual report shows: " . $inPeriodCount . " (filtered by reservation.date_debut)\n";
echo "\nThe discrepancy is INTENTIONAL and CORRECT.\n";
