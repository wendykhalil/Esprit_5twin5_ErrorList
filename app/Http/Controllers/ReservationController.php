<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReservationRequest;
use App\Models\Equipement;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Carbon;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::with(['user', 'equipement'])->latest()->paginate(10);
        return view('reservations.index', compact('reservations'));
    }

    public function create()
    {
        return view('reservations.create', $this->formData());
    }

    public function store(ReservationRequest $request)
    {
        $data = $request->validated();
        $data['prix_total'] = $this->calculerPrix($data);
        Reservation::create($data);

        return redirect()->route('reservations.index')->with('success', 'Réservation ajoutée.');
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['user', 'equipement', 'inspections']);
        return view('reservations.show', compact('reservation'));
    }

    public function edit(Reservation $reservation)
    {
        return view('reservations.edit', $this->formData() + compact('reservation'));
    }

    public function update(ReservationRequest $request, Reservation $reservation)
    {
        $data = $request->validated();
        $data['prix_total'] = $this->calculerPrix($data);
        $reservation->update($data);

        return redirect()->route('reservations.show', $reservation)->with('success', 'Réservation modifiée.');
    }

    public function destroy(Reservation $reservation)
    {
        $reservation->delete();
        return redirect()->route('reservations.index')->with('success', 'Réservation supprimée.');
    }

    private function formData(): array
    {
        return [
            'users' => User::orderBy('name')->get(),
            'equipements' => Equipement::all(),
            'statuts' => Reservation::STATUTS,
        ];
    }

    // Valeur ajoutée : prix = nombre de jours x prix par jour de l'équipement
    // (adapter 'prix_jour' au nom réel de la colonne dans la table equipements)
    private function calculerPrix(array $data): float
    {
        $equipement = Equipement::find($data['equipement_id']);
        $jours = Carbon::parse($data['date_debut'])->diffInDays(Carbon::parse($data['date_fin']));
        return round(max($jours, 1) * ($equipement->prix_jour ?? 0), 2);
    }
}
