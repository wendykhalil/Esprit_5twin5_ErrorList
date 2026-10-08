<?php

namespace App\Http\Controllers;

use App\Http\Requests\InspectionRequest;
use App\Models\Inspection;
use App\Models\Reservation;

class InspectionController extends Controller
{
    public function index()
    {
        $inspections = Inspection::with('reservation.equipment')
            ->whereHas('reservation', fn ($query) => $query->where('user_id', auth()->id()))
            ->latest('date_inspection')
            ->paginate(10);
        return view('inspections.index', compact('inspections'));
    }

    public function create()
    {
        $reservation_id = request('reservation_id');

        return view('inspections.create', $this->formData() + [
            'reservation_id' => $reservation_id,
        ]);
    }

    public function store(InspectionRequest $request)
    {
        $data = $request->validated();
        $inspection = Inspection::create($data);
        $this->verifierLitige($inspection);

        return redirect()->route('reservations.show', $inspection->reservation_id)
            ->with('success', 'Inspection ajoutée.');
    }

    public function show(Inspection $inspection)
    {
        abort_unless($inspection->reservation->user_id === auth()->id(), 403);
        $inspection->load('reservation.equipment', 'reservation.user');
        return view('inspections.show', compact('inspection'));
    }

    public function edit(Inspection $inspection)
    {
        abort_unless($inspection->reservation->user_id === auth()->id(), 403);
        $inspection->load('reservation.equipment');
        return view('inspections.edit', $this->formData() + compact('inspection'));
    }

    public function update(InspectionRequest $request, Inspection $inspection)
    {
        abort_unless($inspection->reservation->user_id === auth()->id(), 403);
        $inspection->load('reservation.equipment');
        $data = $request->validated();
        $inspection->update($data);
        $this->verifierLitige($inspection);

        return redirect()->route('inspections.show', $inspection)->with('success', 'Inspection modifiée.');
    }

    public function destroy(Inspection $inspection)
    {
        abort_unless($inspection->reservation->user_id === auth()->id(), 403);
        $inspection->load('reservation.equipment');
        $inspection->delete();
        return redirect()->route('inspections.index')->with('success', 'Inspection supprimée.');
    }

    private function formData(): array
    {
        return [
            'reservations' => Reservation::with('equipment')
                ->where('user_id', auth()->id())
                ->latest()
                ->get(),
            'types' => Inspection::TYPES,
            'etats' => Inspection::ETATS,
        ];
    }

    // Valeur ajoutée : un équipement endommagé au retour met la réservation en litige
    private function verifierLitige(Inspection $inspection): void
    {
        if ($inspection->type === 'retour' && $inspection->etat === 'endommage') {
            $inspection->reservation->update(['statut' => 'litige']);
        }
    }
}
