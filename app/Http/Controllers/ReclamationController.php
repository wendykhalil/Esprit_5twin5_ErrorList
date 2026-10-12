<?php

namespace App\Http\Controllers;

use App\Events\ReclamationCreated;
use App\Http\Requests\StoreReclamationRequest;
use App\Models\Reclamation;
use App\Models\Reservation;
use App\Models\Equipment;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReclamationController extends Controller
{
    /**
     * Show the form for creating a new reclamation.
     */
    public function create()
    {
        $user = Auth::user();

        // Récupérer les réservations éligibles pour une réclamation
        // Une réservation est éligible si elle a eu lieu (statut: confirmee, en_cours, terminee)
        // Cela exclut les statuts futurs (en_attente) et annulés (annulee, refusee, litige)
        $reservations = Reservation::where('user_id', $user->id)
            ->whereIn('statut', ['confirmee', 'en_cours', 'terminee'])
            ->with('equipment')
            ->orderByDesc('updated_at')
            ->get();

        if ($reservations->isEmpty()) {
            return view('frontend.reclamations.no-reservations');
        }

        return view('frontend.reclamations.create', compact('reservations'));
    }

    /**
     * Store a newly created reclamation in storage.
     */
    public function store(StoreReclamationRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();

        // Vérifier que la réservation appartient à l'utilisateur
        $reservation = Reservation::findOrFail($validated['reservation_id']);
        if ($reservation->user_id !== $user->id) {
            abort(403, 'Cette réservation n\'est pas la vôtre.');
        }

        // Vérifier que CHAQUE équipement d'incident appartient à la réservation
        // Note: Une réservation a exactement UN equipment_id
        // Normalize to integer for comparison to prevent type mismatches
        $reservationEquipmentId = (int) $reservation->equipment_id;
        foreach ($validated['incidents'] as $incidentData) {
            $incidentEquipmentId = (int) $incidentData['equipment_id'];
            if ($incidentEquipmentId !== $reservationEquipmentId) {
                abort(403, 'L\'équipement de l\'incident n\'appartient pas à la réservation.');
            }
        }

        try {
            DB::beginTransaction();

            // Créer la réclamation
            $reclamation = Reclamation::create([
                'reservation_id' => $validated['reservation_id'],
                'user_id' => $user->id,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'status' => 'en_attente',
            ]);

            // Créer les incidents avec calcul des remboursements proposés
            foreach ($validated['incidents'] as $incidentData) {
                $equipment = Equipment::findOrFail($incidentData['equipment_id']);
                
                $proposedRefundAmount = $this->calculateProposedRefund(
                    $incidentData['type_probleme'],
                    $equipment->price_per_day
                );

                $proposedRefundPercentage = match ($incidentData['type_probleme']) {
                    'endommage' => 30.0,
                    'manquant' => 20.0,
                    default => 0.0,
                };

                Incident::create([
                    'reclamation_id' => $reclamation->id,
                    'equipment_id' => $incidentData['equipment_id'],
                    'type_probleme' => $incidentData['type_probleme'],
                    'description' => $incidentData['description'],
                    'damage_percentage' => $incidentData['damage_percentage'] ?? null,
                    'proposed_refund_percentage' => $proposedRefundPercentage,
                    'proposed_refund_amount' => $proposedRefundAmount,
                ]);
            }

            DB::commit();

            // Dispatch event after successful DB commit
            ReclamationCreated::dispatch($reclamation);

            return redirect()->route('reclamations.show', $reclamation->id)
                ->with('success', 'Réclamation créée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création de la réclamation.');
        }
    }

    /**
     * Display the specified reclamation.
     */
    public function show(Reclamation $reclamation)
    {
        $this->authorize('view', $reclamation);

        $reclamation->load(['incidents.equipment', 'reservation.equipment', 'user', 'compensationCode']);

        $totalProposedRefund = $reclamation->calculateTotalRefund();

        return view('frontend.reclamations.show', compact('reclamation', 'totalProposedRefund'));
    }

    /**
     * Display a listing of reclamations for the authenticated user.
     */
    public function index()
    {
        $user = Auth::user();

        $reclamations = Reclamation::where('user_id', $user->id)
            ->with(['reservation.equipment', 'incidents'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('frontend.reclamations.index', compact('reclamations'));
    }

    /**
     * Calculate proposed refund amount based on type and price.
     */
    private function calculateProposedRefund(string $type, ?float $price): float
    {
        if (!$price) {
            return 0;
        }

        return match ($type) {
            'endommage' => $price * 0.30,
            'manquant' => $price * 0.20,
            default => 0,
        };
    }
}
