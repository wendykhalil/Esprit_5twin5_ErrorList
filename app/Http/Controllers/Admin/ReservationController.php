<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReservationAdminRequest;
use App\Models\Equipment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $query = Reservation::with(['user', 'equipment', 'inspections']);

        if ($request->filled('search')) {
            $search = $request->string('search');

            $query->where(function ($query) use ($search) {
                $query->whereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('equipment', fn ($equipment) => $equipment->where('name', 'like', "%{$search}%"));
            });
        }

        $selectedStatus = $request->input('status', 'Tous');
        if ($selectedStatus !== 'Tous') {
            $query->where('statut', $selectedStatus);
        }

        $reservations = $query->latest()->paginate(10)->withQueryString();

        return view('backend.reservations.index', [
            'reservations' => $reservations,
            'statuses' => [
                'Tous' => 'Tous',
                'en_attente' => 'En attente',
                'en_cours' => 'En cours',
                'confirmee' => 'Confirmée',
                'terminee' => 'Terminée',
                'litige' => 'Litige',
                'annulee' => 'Annulée',
                'refusee' => 'Refusée',
            ],
            'search' => $request->input('search', ''),
            'selectedStatus' => $selectedStatus,
        ]);
    }

    public function create()
    {
        return view('backend.reservations.create', $this->formData());
    }

    public function store(ReservationAdminRequest $request)
    {
        $data = $request->validated();
        $data['prix_total'] = $this->calculatePrice($data);
        Reservation::create($data);

        return redirect()->route('admin.reservations.index')->with('success', 'Réservation créée.');
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['user', 'equipment', 'inspections']);

        return view('backend.reservations.show', compact('reservation'));
    }

    public function edit(Reservation $reservation)
    {
        return view('backend.reservations.edit', $this->formData() + compact('reservation'));
    }

    public function update(ReservationAdminRequest $request, Reservation $reservation)
    {
        $data = $request->validated();
        $data['prix_total'] = $this->calculatePrice($data);

        $reservation->update($data);

        return redirect()->route('admin.reservations.show', $reservation)->with('success', 'Réservation mise à jour.');
    }

    /**
     * Accepter une réservation en attente (transition en_attente → confirmee)
     * L'admin doit vérifier que les dates sont toujours disponibles.
     * 
     * IMPORTANT: La livraison n'est plus créée ici.
     * Elle sera créée automatiquement quand le client accepte le contrat.
     */
    public function approve(Reservation $reservation)
    {
        // Vérification 1: Statut doit être "en_attente"
        if ($reservation->statut !== 'en_attente') {
            return redirect()->route('admin.reservations.show', $reservation)
                ->with('error', 'Seules les réservations en attente peuvent être acceptées.');
        }

        // Vérification 2: Vérifier que les dates sont toujours disponibles
        $conflict = Reservation::where('equipment_id', $reservation->equipment_id)
            ->whereNotIn('statut', ['annulee', 'terminee', 'refusee'])
            ->where('id', '!=', $reservation->id)
            ->where('date_debut', '<', $reservation->date_fin)
            ->where('date_fin', '>', $reservation->date_debut)
            ->exists();

        if ($conflict) {
            return redirect()->route('admin.reservations.show', $reservation)
                ->with('error', 'Les dates de cette réservation ne sont plus disponibles. Une autre réservation a été confirmée sur cette période.');
        }

        // Transition: en_attente → confirmee
        $reservation->update(['statut' => 'confirmee']);

        return redirect()->route('admin.reservations.show', $reservation)
            ->with('success', 'Réservation acceptée et confirmée. Le client pourra créer la livraison après acceptation du contrat et paiement.');
    }

    /**
     * Refuser une réservation en attente (transition en_attente → refusee)
     */
    public function reject(Reservation $reservation)
    {
        // Vérification: Statut doit être "en_attente"
        if ($reservation->statut !== 'en_attente') {
            return redirect()->route('admin.reservations.show', $reservation)
                ->with('error', 'Seules les réservations en attente peuvent être refusées.');
        }

        // Transition: en_attente → refusee
        $reservation->update(['statut' => 'refusee']);

        return redirect()->route('admin.reservations.show', $reservation)
            ->with('success', 'Réservation refusée.');
    }

    public function destroy(Reservation $reservation)
    {
        // Sécurité: Empêcher la suppression des réservations confirmées ou liées à un paiement
        if (in_array($reservation->statut, ['confirmee', 'en_cours', 'litige'])) {
            return redirect()->route('admin.reservations.index')
                ->with('error', 'Impossible de supprimer une réservation confirmée ou en cours. Utilisez le refus ou l\'annulation.');
        }

        if ($reservation->payments()->whereNotIn('status', ['failed', 'refunded'])->exists()) {
            return redirect()->route('admin.reservations.index')
                ->with('error', 'Impossible de supprimer une réservation liée à un paiement actif.');
        }

        $reservation->delete();

        return redirect()->route('admin.reservations.index')->with('success', 'Réservation supprimée.');
    }

    private function formData(): array
    {
        return [
            'users' => User::orderBy('name')->get(),
            'equipments' => Equipment::orderBy('name')->get(),
            'statuses' => Reservation::STATUTS,
        ];
    }

    /**
     * Calcule le prix total d'une réservation.
     * Prix = nombre de jours × prix par jour de l'équipement
     *
     * @param array $data Les données validées
     * @return float Le prix total arrondi à 2 décimales
     */
    private function calculatePrice(array $data): float
    {
        $equipment = Equipment::findOrFail($data['equipment_id']);
        $days = Carbon::parse($data['date_debut'])->diffInDays(Carbon::parse($data['date_fin']));

        $pricePerDay = $equipment->price_per_day ?? $equipment->prix_jour ?? $equipment->price ?? 0;

        return round(max($days, 1) * $pricePerDay, 2);
    }
}
