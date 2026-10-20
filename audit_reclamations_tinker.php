use Carbon\Carbon;
use App\Models\Reclamation;
use App\Models\Incident;

$startDate = Carbon::parse('2026-01-01');
$endDate = Carbon::parse('2026-10-20');

echo "\n=== AUDIT: Ecart entre admin/reclamations (14) et rapport annuel (6) ===\n\n";

// 1. All reclamations
$allReclamations = Reclamation::all();
echo "STEP 1: TOTAL RECLAMATIONS (no filter) = " . $allReclamations->count() . "\n";

// 2. Reclamations by reservation date_debut
$reportReclamations = Reclamation::whereHas('reservation', function ($q) use ($startDate, $endDate) {
    $q->whereBetween('date_debut', [$startDate, $endDate]);
})->get();
echo "STEP 2: REPORT METHOD (whereBetween date_debut) = " . $reportReclamations->count() . "\n\n";

// 3. Detailed comparison
echo "ALL RECLAMATIONS:\n";
foreach ($allReclamations as $r) {
    $res = $r->reservation;
    $inPeriod = $res->date_debut >= $startDate && $res->date_debut <= $endDate;
    $status = $inPeriod ? "IN" : "OUT";
    echo "ID " . $r->id . ": status=" . $r->status . ", res_date_debut=" . $res->date_debut->format('d/m/Y') . " (" . $status . ")\n";
}

echo "\nOUTSIDE PERIOD:\n";
$outside = $allReclamations->filter(function($r) use ($startDate, $endDate) {
    return !($r->reservation->date_debut >= $startDate && $r->reservation->date_debut <= $endDate);
});
echo "Count: " . $outside->count() . "\n";
foreach ($outside as $r) {
    echo "ID " . $r->id . ": " . $r->reservation->date_debut->format('d/m/Y') . "\n";
}
