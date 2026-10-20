<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ReportGenerationService;
use Carbon\Carbon;

$start = Carbon::parse('2026-01-01');
$end = Carbon::now()->endOfDay();

$reportService = app(ReportGenerationService::class);

echo "=== DETERMINISTIC SUMMARY TEST ===\n\n";

$stats = $reportService->aggregateStatistics($start, $end);

echo "--- DATA INPUT ---\n";
echo "Reservations: {$stats['reservations']['total']}\n";
echo "Completed: {$stats['reservations']['completed']}\n";
echo "Duration: {$stats['reservations']['avg_duration_days']} days\n";
echo "Revenue: {$stats['revenue']['total']} TND\n";
echo "Reclamations: {$stats['reclamations']['total']}\n\n";

echo "--- GENERATED SUMMARY ---\n";
$summary = $reportService->generateDeterministicSummary($stats, $start, $end);
echo $summary . "\n\n";

echo "--- VERIFICATION ---\n";
if ($stats['reservations']['completed'] == 0) {
    if (stripos($summary, 'Non disponible') !== false) {
        echo "✅ Duration correctly marked as unavailable\n";
    } else {
        echo "❌ Duration should be marked as unavailable\n";
    }
    
    if (stripos($summary, 'aucune réservation complétée') !== false) {
        echo "✅ Explicitly states no completed reservations\n";
    } else {
        echo "❌ Should explicitly state no completed reservations\n";
    }
}

if (strpos($summary, '2809.4') !== false || strpos($summary, '2809,4') !== false) {
    echo "✅ Revenue figure correctly included\n";
} else {
    echo "❌ Revenue figure missing\n";
}

if (strpos($summary, '31.58') !== false) {
    echo "✅ Reclamation rate correctly included\n";
} else {
    echo "❌ Reclamation rate missing\n";
}

echo "\n=== END TEST ===\n";
