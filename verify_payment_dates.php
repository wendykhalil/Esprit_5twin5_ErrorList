<?php
// Script de vérification - Examinons comment les paiements sont réellement filtrés
require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

use App\Models\Payment;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

$app->make('Illuminate\Contracts\Http\Kernel')->handle(
    $request = \Illuminate\Http\Request::capture()
);

echo "=== VÉRIFICATION: Filtrage des paiements par date ===\n\n";

// 1. Vérifier la structure des données
echo "1. STRUCTURE DES DONNÉES\n";
echo "------------------------\n";

// Check if payment_date column exists
$columns = DB::select("PRAGMA table_info(payments)");
$hasPaymentDate = false;
foreach ($columns as $col) {
    if ($col->name === 'payment_date') {
        $hasPaymentDate = true;
        break;
    }
}
echo "payment_date column exists: " . ($hasPaymentDate ? "YES ✓" : "NO ✗") . "\n\n";

// 2. Examiner quelques paiements réels
echo "2. PAIEMENTS RÉELS EN BASE DE DONNÉES\n";
echo "--------------------------------------\n";

$payments = Payment::with('reservation')->limit(5)->get();

if ($payments->isEmpty()) {
    echo "Aucun paiement trouvé en base.\n";
} else {
    foreach ($payments as $payment) {
        echo "\nPayment ID: {$payment->id}\n";
        echo "  - Status: {$payment->status}\n";
        echo "  - Amount: {$payment->amount}\n";
        echo "  - created_at: {$payment->created_at}\n";
        echo "  - payment_date: " . ($payment->payment_date ? $payment->payment_date : "(NULL)") . "\n";
        
        if ($payment->reservation) {
            echo "  - Reservation ID: {$payment->reservation_id}\n";
            echo "  - Res date_debut: {$payment->reservation->date_debut}\n";
            echo "  - Res date_fin: {$payment->reservation->date_fin}\n";
            
            // Vérifier si la payment_date est différente de date_debut
            if ($payment->payment_date && $payment->reservation) {
                $payDate = Carbon::parse($payment->payment_date);
                $resStart = Carbon::parse($payment->reservation->date_debut);
                $difference = $payDate->diffInDays($resStart);
                if ($difference !== 0) {
                    echo "  ⚠️ IMPORTANT: payment_date diffère de date_debut de {$difference} jours\n";
                }
            }
        }
    }
}

// 3. Vérifier le problème de filtrage
echo "\n\n3. IMPACT DU FILTRAGE PAR date_debut\n";
echo "-------------------------------------\n";

// Créer une période de test
$startDate = Carbon::parse('2026-10-01');
$endDate = Carbon::parse('2026-10-31');

echo "Test de période: {$startDate->format('Y-m-d')} à {$endDate->format('Y-m-d')}\n\n";

// Méthode actuelle (par date_debut de réservation)
$countByReservationDateDebut = Payment::whereHas('reservation', function ($q) use ($startDate, $endDate) {
    $q->whereBetween('date_debut', [$startDate, $endDate]);
})->count();

echo "Paiements filtrés par date_debut de réservation: {$countByReservationDateDebut}\n";

// Méthode alternative (si payment_date était utilisée)
if ($hasPaymentDate) {
    $countByPaymentDate = Payment::whereNotNull('payment_date')
        ->whereBetween('payment_date', [$startDate->startOfDay(), $endDate->endOfDay()])
        ->count();
    
    echo "Paiements filtrés par payment_date réelle: {$countByPaymentDate}\n";
    
    if ($countByPaymentDate !== $countByReservationDateDebut) {
        echo "\n⚠️ DISCREPANCY DETECTÉE!\n";
        echo "   Les deux méthodes donnent des résultats DIFFÉRENTS.\n";
        echo "   Cela peut indiquer un problème de filtrage temporel.\n";
    } else {
        echo "\n✓ Les deux méthodes donnent le même résultat.\n";
    }
}

// 4. Réclamations et incidents
echo "\n\n4. RÉCLAMATIONS ET INCIDENTS\n";
echo "-----------------------------\n";

$reclamations = Payment::with('reservation.reclamations.incidents')->limit(1)->get();
echo "Structure: Reservation → has many Reclamations → has many Incidents\n";

$reclamation = \App\Models\Reclamation::with('incidents')->first();
if ($reclamation) {
    echo "\nReclamation ID: {$reclamation->id}\n";
    echo "  - Status: {$reclamation->status}\n";
    echo "  - created_at: {$reclamation->created_at}\n";
    echo "  - refund_approved_at: " . ($reclamation->refund_approved_at ? $reclamation->refund_approved_at : "(NULL)") . "\n";
    echo "  - refund_amount: {$reclamation->refund_amount}\n";
    echo "  - Nombre d'incidents: {$reclamation->incidents->count()}\n";
}

// 5. Coûts
echo "\n\n5. DONNÉES DE COÛTS\n";
echo "-------------------\n";

$equipment = \App\Models\Equipment::first();
if ($equipment) {
    $refCols = ['price_per_day', 'cost_per_day', 'acquisition_price', 'maintenance_cost'];
    foreach ($refCols as $col) {
        $value = $equipment->getAttribute($col) ?? "(N/A)";
        $status = ($col === 'price_per_day' && $value !== '(N/A)') ? "✓ EXISTS" : "✗ N/A";
        echo "  - {$col}: {$status}\n";
    }
}

echo "\n";
echo "=== FIN DE VÉRIFICATION ===\n";
?>
