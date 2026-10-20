#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Reservation;
use App\Models\Payment;
use Carbon\Carbon;

echo "=== EQUIPMENT RANKINGS AUDIT FOR OCTOBER 2026 ===\n\n";

$startDate = Carbon::parse('2026-10-01');
$endDate = Carbon::parse('2026-10-31');

// CURRENT: Rankings sorted by revenue
echo "--- CURRENT RANKING (Sorted by Revenue) ---\n";
$byRevenue = Reservation::whereBetween('date_debut', [$startDate, $endDate])
    ->join('equipment', 'reservations.equipment_id', '=', 'equipment.id')
    ->selectRaw('
        equipment.id,
        equipment.name,
        equipment.price_per_day,
        COUNT(DISTINCT reservations.id) as reservation_count
    ')
    ->groupBy('equipment.id', 'equipment.name', 'equipment.price_per_day')
    ->limit(20)
    ->get();

$equipmentIds = $byRevenue->pluck('id')->toArray();

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

$deduplicatedById = [];
foreach ($byRevenue as $item) {
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

$sortedByRevenue = collect($deduplicatedById)
    ->sortByDesc('revenue')
    ->take(10)
    ->values()
    ->toArray();

foreach ($sortedByRevenue as $i => $equip) {
    echo sprintf(
        "%2d. %-35s (ID:%2d) - %2d reservations, %8.2f TND\n",
        $i + 1,
        substr($equip['name'], 0, 35),
        $equip['id'],
        $equip['reservation_count'],
        $equip['revenue']
    );
}

// PROPOSED: Rankings sorted by reservation count
echo "\n--- PROPOSED RANKING (Sorted by Reservation Count) ---\n";
$sortedByReservations = collect($deduplicatedById)
    ->sortByDesc('reservation_count')
    ->take(10)
    ->values()
    ->toArray();

foreach ($sortedByReservations as $i => $equip) {
    echo sprintf(
        "%2d. %-35s (ID:%2d) - %2d reservations, %8.2f TND\n",
        $i + 1,
        substr($equip['name'], 0, 35),
        $equip['id'],
        $equip['reservation_count'],
        $equip['revenue']
    );
}

echo "\n--- COMPARISON ---\n";
echo "By Revenue - Top 3:\n";
foreach (array_slice($sortedByRevenue, 0, 3) as $i => $e) {
    echo "  " . ($i+1) . ". ID {$e['id']}: {$e['revenue']} TND\n";
}
echo "\nBy Reservations - Top 3:\n";
foreach (array_slice($sortedByReservations, 0, 3) as $i => $e) {
    echo "  " . ($i+1) . ". ID {$e['id']}: {$e['reservation_count']} réservations\n";
}
