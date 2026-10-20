#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Reclamation;
use App\Models\Incident;
use App\Services\ReportGenerationService;
use Carbon\Carbon;

echo "=== RECLAMATIONS & INCIDENTS AUDIT FOR 2026 ===\n\n";

// Verify reclamations count for 2026
$reclamations2026 = Reclamation::whereHas('reservation', function ($q) {
    $q->whereBetween('date_debut', ['2026-01-01', '2026-12-31']);
})->count();

echo "Total reclamations for 2026 reservations: $reclamations2026\n";

// Count by status
$byStatus = Reclamation::whereHas('reservation', function ($q) {
    $q->whereBetween('date_debut', ['2026-01-01', '2026-12-31']);
})->selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status');

echo "Reclamations by status:\n";
foreach ($byStatus as $status => $count) {
    echo "  $status: $count\n";
}

// Count incidents for 2026
$incidents2026 = Incident::whereHas('reclamation.reservation', function ($q) {
    $q->whereBetween('date_debut', ['2026-01-01', '2026-12-31']);
})->count();

echo "\nTotal incidents for 2026 reclamations: $incidents2026\n";

// Count by type
$byType = Incident::whereHas('reclamation.reservation', function ($q) {
    $q->whereBetween('date_debut', ['2026-01-01', '2026-12-31']);
})->selectRaw('type_probleme, COUNT(*) as count')->groupBy('type_probleme')->pluck('count', 'type_probleme');

echo "Incidents by type:\n";
foreach ($byType as $type => $count) {
    echo "  $type: $count\n";
}

// Check current report data
echo "\n--- Current Report Stats (Month View) ---\n";
$startDate = Carbon::parse('2026-10-01');
$endDate = Carbon::parse('2026-10-31');
$reportService = $app->make(ReportGenerationService::class);
$stats = $reportService->aggregateStatistics($startDate, $endDate);

echo "Report shows for October 2026:\n";
echo "  Reclamations total: " . $stats['reclamations']['total'] . "\n";
echo "  Incidents total: " . $stats['reclamations']['incidents_total'] . "\n";
echo "  By status: " . json_encode($stats['reclamations']['by_status']) . "\n";
echo "  By type: " . json_encode($stats['reclamations']['by_type']) . "\n";

// Equipment ranking check - focus on ID 13
echo "\n--- EQUIPMENT RANKINGS CHECK ---\n";
echo "Top 10 equipment by revenue (October 2026):\n";
foreach ($stats['equipment_rankings'] as $i => $equip) {
    echo sprintf(
        "  %d. %s (ID: %d) - %d réservations, %.2f TND revenue\n",
        $i + 1,
        $equip['name'],
        $equip['id'],
        $equip['reservation_count'],
        $equip['revenue']
    );
}

// Specifically check ID 13
echo "\n--- EQUIPMENT ID 13 DEEP DIVE ---\n";
$equip13 = \App\Models\Equipment::find(13);
if ($equip13) {
    echo "Equipment 13: {$equip13->name}\n";
    echo "Price per day: {$equip13->price_per_day} TND\n";
    
    // Count reservations for ID 13 in October 2026
    $reservations13 = \App\Models\Reservation::where('equipment_id', 13)
        ->whereBetween('date_debut', [$startDate, $endDate])
        ->count();
    echo "Reservations in Oct 2026: $reservations13\n";
    
    // Get all payments for these reservations
    $payments13 = \App\Models\Payment::where('status', 'paid')
        ->whereHas('reservation', function ($q) {
            $q->where('equipment_id', 13)
                ->whereBetween('date_debut', ['2026-10-01', '2026-10-31']);
        })
        ->get();
    
    echo "Paid payments for ID 13 in Oct 2026: " . count($payments13) . "\n";
    $totalRevenue13 = $payments13->sum('amount');
    echo "Total revenue: $totalRevenue13 TND\n";
    
    if (count($payments13) > 0) {
        echo "Payment details:\n";
        foreach ($payments13 as $p) {
            echo "  - Payment ID {$p->id}: {$p->amount} TND, status: {$p->status}, date: {$p->payment_date}\n";
        }
    }
} else {
    echo "Equipment ID 13 not found\n";
}
