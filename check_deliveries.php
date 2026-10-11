<?php
require 'bootstrap/app.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Delivery;
use App\Models\Reservation;

echo "=== Vérification des Livraisons ===\n\n";

$deliveries = Delivery::all();
echo "Total livraisons: " . count($deliveries) . "\n\n";

if (count($deliveries) > 0) {
    foreach ($deliveries as $delivery) {
        echo "Livraison ID: {$delivery->id}\n";
        echo "  - Réservation ID: {$delivery->reservation_id}\n";
        echo "  - Utilisateur ID: {$delivery->user_id}\n";
        echo "  - Équipement: {$delivery->equipment->name}\n";
        echo "  - Statut: {$delivery->status}\n";
        echo "  - Date livraison prévue: {$delivery->planned_delivery_date}\n";
        echo "\n";
    }
}

// Chercher spécifiquement la réservation #13
echo "=== Vérification Réservation #13 ===\n";
$reservation = Reservation::find(13);
if ($reservation) {
    echo "Réservation #13 trouvée\n";
    echo "  - Utilisateur: {$reservation->user->name}\n";
    echo "  - Statut: {$reservation->statut}\n";
    $delivery = Delivery::where('reservation_id', 13)->first();
    if ($delivery) {
        echo "  - Livraison associée ID: {$delivery->id}\n";
        echo "  - Statut livraison: {$delivery->status}\n";
    } else {
        echo "  - AUCUNE LIVRAISON ASSOCIÉE\n";
    }
} else {
    echo "Réservation #13 NON TROUVÉE\n";
}
