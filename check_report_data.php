<?php
require_once __DIR__ . '/bootstrap/app.php';

use App\Models\Reservation;
use App\Models\Reclamation;
use App\Models\Payment;
use App\Models\Incident;
use Carbon\Carbon;

// Get app instance
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== LATEST RESERVATIONS ===\n";
$reservations = Reservation::select('id', 'date_debut', 'date_fin', 'statut', 'prix_total', 'created_at')
    ->orderBy('id', 'desc')
    ->limit(5)
    ->get();
foreach ($reservations as $r) {
    echo "ID: {$r->id} | date_debut: {$r->date_debut} | created_at: {$r->created_at->format('Y-m-d H:i')} | statut: {$r->statut} | prix: {$r->prix_total}\n";
}

echo "\n=== LATEST PAYMENTS ===\n";
$payments = Payment::select('id', 'reservation_id', 'amount', 'status', 'payment_date', 'created_at')
    ->orderBy('id', 'desc')
    ->limit(5)
    ->get();
foreach ($payments as $p) {
    echo "ID: {$p->id} | res_id: {$p->reservation_id} | amount: {$p->amount} | status: {$p->status} | payment_date: " . ($p->payment_date ? $p->payment_date->format('Y-m-d H:i') : 'NULL') . "\n";
}

echo "\n=== LATEST RECLAMATIONS ===\n";
$reclamations = Reclamation::select('id', 'reservation_id', 'title', 'status', 'created_at', 'refund_amount')
    ->orderBy('id', 'desc')
    ->limit(5)
    ->get();
foreach ($reclamations as $rec) {
    $res = $rec->reservation;
    $dateDebut = $res ? $res->date_debut->format('Y-m-d') : 'N/A';
    echo "ID: {$rec->id} | res_id: {$rec->reservation_id} | res_date_debut: {$dateDebut} | status: {$rec->status} | created_at: {$rec->created_at->format('Y-m-d H:i')}\n";
}

echo "\n=== LATEST INCIDENTS ===\n";
$incidents = Incident::select('id', 'reclamation_id', 'type_probleme', 'created_at')
    ->orderBy('id', 'desc')
    ->limit(5)
    ->get();
foreach ($incidents as $inc) {
    echo "ID: {$inc->id} | rec_id: {$inc->reclamation_id} | type: {$inc->type_probleme} | created_at: {$inc->created_at->format('Y-m-d H:i')}\n";
}

echo "\n=== PERIOD 19/10 - 21/10 ANALYSIS ===\n";
$start = Carbon::parse('2026-10-19');
$end = Carbon::parse('2026-10-21');

$resInPeriod = Reservation::whereBetween('date_debut', [$start, $end])->count();
$recInPeriod = Reclamation::whereHas('reservation', function($q) use ($start, $end) {
    $q->whereBetween('date_debut', [$start, $end]);
})->count();

$payInPeriod = Payment::where('status', 'paid')
    ->whereNotNull('payment_date')
    ->whereBetween('payment_date', [$start->startOfDay(), $end->endOfDay()])
    ->sum('amount');

$incInPeriod = Incident::whereHas('reclamation', function($q) use ($start, $end) {
    $q->whereHas('reservation', function($q2) use ($start, $end) {
        $q2->whereBetween('date_debut', [$start, $end]);
    });
})->count();

echo "Reservations (date_debut in period): $resInPeriod\n";
echo "Reclamations (linked to reservations with date_debut in period): $recInPeriod\n";
echo "Incidents (linked to reclamations linked to reservations with date_debut in period): $incInPeriod\n";
echo "Paid Revenue (payment_date in period): $payInPeriod TND\n";

// Check data matching problem statement
echo "\n=== VERIFY PROBLEM STATEMENT ===\n";
echo "Looking for Reservation #074 (22-25 Oct, 441.69 TND):\n";
$res074 = Reservation::find(74);
if ($res074) {
    echo "  Found: date_debut={$res074->date_debut}, date_fin={$res074->date_fin}, prix_total={$res074->prix_total}\n";
} else {
    echo "  NOT FOUND\n";
}

echo "\nLooking for Payment #31 (441.69 TND, 21 Oct):\n";
$pay31 = Payment::find(31);
if ($pay31) {
    echo "  Found: amount={$pay31->amount}, status={$pay31->status}, payment_date=" . ($pay31->payment_date ? $pay31->payment_date->format('Y-m-d') : 'NULL') . "\n";
} else {
    echo "  NOT FOUND\n";
}

echo "\nLooking for Reclamation #17 (manque equipement):\n";
$rec17 = Reclamation::find(17);
if ($rec17) {
    $res = $rec17->reservation;
    echo "  Found: title={$rec17->title}, status={$rec17->status}, res_date_debut={$res->date_debut}, created_at={$rec17->created_at->format('Y-m-d H:i')}\n";
} else {
    echo "  NOT FOUND\n";
}
