<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ReportGenerationService;
use Carbon\Carbon;

$start = Carbon::parse('2026-01-01');
$end = Carbon::now()->endOfDay();

$reportService = app(ReportGenerationService::class);

echo "=== AI SUMMARY TEST ===\n\n";

$stats = $reportService->aggregateStatistics($start, $end);

$context = $reportService->buildSummaryContext($stats, $start, $end);

echo "--- CONTEXT SENT TO OLLAMA ---\n";
echo $context . "\n\n";

echo "--- CALCULATED METRICS ---\n";
$totalReservations = $stats['reservations']['total'];
$completedReservations = $stats['reservations']['completed'];
$completionRate = $totalReservations > 0 ? round(($completedReservations / $totalReservations) * 100, 1) : 0;
echo "Completion Rate: {$completionRate}%\n";
echo "Average Duration: {$stats['reservations']['avg_duration_days']} days\n";
echo "Completed Reservations: {$completedReservations}\n\n";

echo "--- EQUIPMENT LIST SENT TO OLLAMA ---\n";
$equipmentList = $reportService->formatEquipmentList($stats['equipment_rankings']);
echo $equipmentList . "\n\n";

echo "--- POTENTIAL CONTRADICTIONS ---\n";
if ($completionRate == 0 && $completedReservations == 0) {
    echo "⚠️  RISK: AI might say '0% completed' but also mention 'completed rentals'\n";
}

if ($stats['reservations']['avg_duration_days'] == 0 && $completedReservations == 0) {
    echo "⚠️  RISK: AI might present '0 days' as a measured value instead of 'data unavailable'\n";
}

echo "\n=== END TEST ===\n";
