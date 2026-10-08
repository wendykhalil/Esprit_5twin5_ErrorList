<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReservationRequest;
use App\Models\Equipment;
use App\Models\Reservation;
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
        return view('reservations.create', $this->formData());
    }

    public function store(ReservationRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['statut'] = 'en_attente';
        $data['prix_total'] = $this->calculerPrix($data);
        Reservation::create($data);

        return redirect()->route('reservations.index')->with('success', 'Réservation ajoutée.');
    }

    public function show(Reservation $reservation)
    {
        abort_unless($reservation->user_id === auth()->id(), 403);
        $reservation->load(['user', 'equipment', 'inspections']);

        return view('reservations.show', compact('reservation'));
    }

    public function edit(Reservation $reservation)
    {
        abort_unless($reservation->user_id === auth()->id(), 403);
        return view('reservations.edit', $this->formData() + compact('reservation'));
    }

    public function update(ReservationRequest $request, Reservation $reservation)
    {
        abort_unless($reservation->user_id === auth()->id(), 403);
        $data = $request->validated();
        $data['prix_total'] = $this->calculerPrix($data);
        $reservation->update($data);

        return redirect()->route('reservations.show', $reservation)->with('success', 'Réservation modifiée.');
    }

    public function destroy(Reservation $reservation)
    {
        abort_unless($reservation->user_id === auth()->id(), 403);
        $reservation->delete();
        return redirect()->route('reservations.index')->with('success', 'Réservation supprimée.');
    }

    private function formData(): array
    {
        return [
            'equipments' => Equipment::all(),
        ];
    }

    // Valeur ajoutée : prix = nombre de jours x prix par jour de l'équipement
    private function calculerPrix(array $data): float
    {
        $equipment = Equipment::find($data['equipment_id']);
        $jours = Carbon::parse($data['date_debut'])->diffInDays(Carbon::parse($data['date_fin']));
        return round(max($jours, 1) * ($equipment->price_per_day ?? $equipment->prix_jour ?? $equipment->price ?? 0), 2);
    }
}
