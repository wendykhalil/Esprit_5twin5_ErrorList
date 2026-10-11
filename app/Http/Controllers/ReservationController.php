<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReservationRequest;
use App\Models\Equipment;
use App\Models\Reservation;
use App\Services\ApplyReservationCompensationService;
use Illuminate\Support\Carbon;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::with('equipment')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);
        return view('reservations.index', compact('reservations'));
    }

    public function create()
    {
        $formData = $this->formData();

        // Récupérer les paramètres pré-remplis depuis l'URL (provenant de la page équipement)
        $preFilledData = [
            'equipment_id' => request()->query('equipment_id'),
            'start_date' => request()->query('start_date'),
            'days' => request()->query('days'),
        ];

        return view('reservations.create', $formData + compact('preFilledData'));
    }

    public function store(ReservationRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['statut'] = 'en_attente';
        $data['prix_total'] = $this->calculerPrix($data);

        $reservation = Reservation::create($data);

        // Apply compensation code if provided
        if (!empty($data['compensation_code'])) {
            $service = new ApplyReservationCompensationService();
            try {
                $reservation = $service->apply($reservation, $data['compensation_code']);
                $reservation->refresh(); // Ensure all fields are up-to-date from database
                $message = 'Réservation créée avec succès. Code de compensation appliqué. En attente de confirmation de l\'administrateur.';
            } catch (\Exception $e) {
                // Code application failed, but reservation was created
                // This should not happen because FormRequest validates the code, but handle gracefully
                $message = 'Réservation créée, mais le code de compensation n\'a pas pu être appliqué: ' . $e->getMessage();
            }
        } else {
            $message = 'Réservation créée avec succès. En attente de confirmation de l\'administrateur.';
        }

        return redirect()->route('reservations.show', $reservation)->with('success', $message);
    }

    public function show(Reservation $reservation)
    {
        abort_unless($reservation->user_id === auth()->id(), 403);
        $reservation->load(['user', 'equipment', 'inspections', 'contract']);

        return view('reservations.show', compact('reservation'));
    }

    public function edit(Reservation $reservation)
    {
        abort_unless($reservation->user_id === auth()->id(), 403);

        // Restriction: Le client ne peut modifier que les réservations en attente
        abort_unless($reservation->statut === 'en_attente', 403, 'Vous ne pouvez modifier que les réservations en attente.');

        return view('reservations.edit', $this->formData() + compact('reservation'));
    }

    public function update(ReservationRequest $request, Reservation $reservation)
    {
        abort_unless($reservation->user_id === auth()->id(), 403);

        // Restriction: Le client ne peut modifier que les réservations en attente
        abort_unless($reservation->statut === 'en_attente', 403, 'Vous ne pouvez modifier que les réservations en attente.');

        $data = $request->validated();
        $message = 'Réservation modifiée.';

        // Calculate new price based on date changes
        $newPrix = $this->calculerPrix($data);

        // Store the base price before applying compensation code
        // If code is being removed, use the current base_price; otherwise use new calculated price
        if ($reservation->compensation_code_id && empty($data['compensation_code'])) {
            // Remove old compensation code first
            $service = new ApplyReservationCompensationService();
            $reservation = $service->remove($reservation);
            $reservation->refresh();
            // Update with new price and no code
            $data['prix_total'] = $newPrix;
            $reservation->update($data);
        } elseif (!empty($data['compensation_code']) && $reservation->compensation_code_id === null) {
            // Applying a new code: use calculated price as base
            $data['prix_total'] = $newPrix;
            $reservation->update($data);
            $service = new ApplyReservationCompensationService();
            try {
                $reservation = $service->apply($reservation, $data['compensation_code']);
                $reservation->refresh();
                $message = 'Réservation modifiée. Code de compensation appliqué.';
            } catch (\Exception $e) {
                $message = 'Réservation modifiée, mais le code de compensation n\'a pas pu être appliqué: ' . $e->getMessage();
            }
        } else {
            // No code changes, just update price
            $data['prix_total'] = $newPrix;
            $reservation->update($data);
        }

        return redirect()->route('reservations.show', $reservation)->with('success', $message);
    }

    /**
     * Annuler une réservation (changement de statut vers 'annulee')
     * Disponible uniquement pour les réservations en attente ou confirmées
     */
    public function cancel(Reservation $reservation)
    {
        abort_unless($reservation->user_id === auth()->id(), 403);

        // Annulation autorisée pour: en_attente, confirmee
        abort_unless(
            in_array($reservation->statut, ['en_attente', 'confirmee']),
            403,
            'Cette réservation ne peut pas être annulée.'
        );

        $reservation->update(['statut' => 'annulee']);

        return redirect()->route('reservations.index')->with('success', 'Réservation annulée.');
    }

    public function destroy(Reservation $reservation)
    {
        abort_unless($reservation->user_id === auth()->id(), 403);

        // Suppression interdite si une réservation est confirmée ou liée à un paiement réussi
        abort_unless(
            $reservation->statut === 'en_attente',
            403,
            'Vous ne pouvez supprimer que les réservations en attente. Utilisez l\'annulation pour les autres statuts.'
        );

        $reservation->delete();
        return redirect()->route('reservations.index')->with('success', 'Réservation supprimée.');
    }

    private function formData(): array
    {
        // Exclure les équipements de l'utilisateur connecté
        $equipments = Equipment::where('user_id', '!=', auth()->id())->get();

        return [
            'equipments' => $equipments,
        ];
    }

    /**
     * Calcule le prix total d'une réservation.
     * Prix = nombre de jours × prix par jour de l'équipement
     *
     * @param array $data Les données validées contenant equipment_id, date_debut, date_fin
     * @return float Le prix total arrondi à 2 décimales
     * @throws \Exception Si l'équipement n'existe pas ou n'a pas de prix défini
     */
    private function calculerPrix(array $data): float
    {
        $equipment = Equipment::find($data['equipment_id']);

        if (!$equipment) {
            throw new \Exception('Équipement non trouvé.');
        }

        $pricePerDay = $equipment->price_per_day ?? $equipment->prix_jour ?? $equipment->price ?? 0;

        if ($pricePerDay <= 0) {
            throw new \Exception('Prix par jour invalide pour cet équipement.');
        }

        $jours = Carbon::parse($data['date_debut'])->diffInDays(Carbon::parse($data['date_fin']));
        return round(max($jours, 1) * $pricePerDay, 2);
    }
}
