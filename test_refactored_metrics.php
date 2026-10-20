<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ReportGenerationService;
use Carbon\Carbon;

$start = Carbon::parse('2026-01-01');
$end = Carbon::now()->endOfDay();

$reportService = app(ReportGenerationService::class);

echo "=== REFACTORED METRICS TEST ===\n\n";

try {
    $stats = $reportService->aggregateStatistics($start, $end);

    echo "✅ Statistics aggregated successfully\n\n";
    
    echo "--- RESERVATIONS ---\n";
    echo "Total: {$stats['reservations']['total']}\n";
    echo "Completed: {$stats['reservations']['completed']}\n";
    echo "By Status: " . json_encode($stats['reservations']['by_status']) . "\n";
    echo "Avg Duration: {$stats['reservations']['avg_duration_days']} days\n\n";
    
    echo "--- REVENUE (NEW) ---\n";
    echo "Collected: {$stats['revenue']['collected']} TND\n";
    echo "Pending: {$stats['revenue']['pending']} TND\n";
    echo "Failed: {$stats['revenue']['failed']} TND\n";
    echo "Refunded: {$stats['revenue']['refunded']} TND\n";
    echo "Net: {$stats['revenue']['net']} TND\n";
    echo "By Status: " . json_encode($stats['revenue']['by_status']) . "\n\n";
    
    echo "--- RECLAMATIONS (NEW) ---\n";
    echo "Total Reclamations: {$stats['reclamations']['total']}\n";
    echo "Total Incidents: {$stats['reclamations']['incidents_total']}\n";
    echo "Approved: {$stats['reclamations']['approved']}\n";
    echo "Rejected: {$stats['reclamations']['rejected']}\n";
    echo "Rate: {$stats['reclamations']['rate']}%\n";
    echo "By Status: " . json_encode($stats['reclamations']['by_status']) . "\n";
    echo "By Type: " . json_encode($stats['reclamations']['by_type']) . "\n\n";
    
    echo "--- EQUIPMENT ---\n";
    echo "Top Equipment (count): " . count($stats['equipment_rankings']) . "\n";
    if (count($stats['equipment_rankings']) > 0) {
        echo "Top 3:\n";
        foreach (array_slice($stats['equipment_rankings'], 0, 3) as $i => $eq) {
            echo "  " . ($i + 1) . ". {$eq['name']} (ID {$eq['id']}): {$eq['reservation_count']} reservations, {$eq['revenue']} TND\n";
        }
    }
    
    echo "\n✅ ALL METRICS CALCULATED SUCCESSFULLY\n";

} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
