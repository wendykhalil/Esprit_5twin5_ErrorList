<?php
require 'bootstrap/app.php';

use App\Models\Reservation;
use App\Models\Payment;
use App\Models\Contract;
use App\Models\Delivery;

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$kernel->bootstrap();

// Check reservation #13
$reservation = Reservation::find(13);
echo "=== RESERVATION #13 ===\n";
echo "Found: " . ($reservation ? "YES" : "NO") . "\n";
if ($reservation) {
    echo "Statut: " . $reservation->statut . "\n";
    echo "Equipment: " . ($reservation->equipment?->name ?? 'N/A') . "\n";
    echo "Date debut: " . $reservation->date_debut . "\n";
    echo "Date fin: " . $reservation->date_fin . "\n";
    echo "Prix total: " . $reservation->prix_total . "\n";
}

// Check payment #13
$payment = Payment::find(13);
echo "\n=== PAYMENT #13 ===\n";
echo "Found: " . ($payment ? "YES" : "NO") . "\n";
if ($payment) {
    echo "Status: " . $payment->status . "\n";
    echo "Method: " . $payment->method . "\n";
    echo "Amount: " . $payment->amount . "\n";
    echo "Reservation ID: " . $payment->reservation_id . "\n";
}

// Check contract for reservation #13
$contract = Contract::where('reservation_id', 13)->first();
echo "\n=== CONTRACT FOR RESERVATION #13 ===\n";
if ($contract) {
    echo "Contract ID: " . $contract->id . "\n";
    echo "Contract Number: " . $contract->contract_number . "\n";
    echo "Status: " . $contract->status . "\n";
} else {
    echo "NO CONTRACT FOUND\n";
}

// Check delivery for reservation #13
$delivery = Delivery::where('reservation_id', 13)->first();
echo "\n=== DELIVERY FOR RESERVATION #13 ===\n";
if ($delivery) {
    echo "Delivery ID: " . $delivery->id . "\n";
    echo "Status: " . $delivery->status . "\n";
} else {
    echo "NO DELIVERY FOUND\n";
}
