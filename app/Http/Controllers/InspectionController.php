<?php

namespace App\Http\Controllers;

use App\Http\Requests\InspectionRequest;
use App\Models\Inspection;
use App\Models\Reservation;

class InspectionController extends Controller
{
    public function index()
    {
        $inspections = Inspection::with('reservation.equipement')->latest('date_inspection')->paginate(10);
        return view('inspections.index', compact('inspections'));
    }

    public function create()
    {
        return view('inspections.create', $this->formData());
    }

    public function store(InspectionRequest $request)
    {
        $inspection = Inspection::create($request->validated());
        $this->verifierLitige($inspection);

        return redirect()->route('inspections.index')->with('success', 'Inspection ajoutée.');
    }

    public function show(Inspection $inspection)
    {
        $inspection->load('reservation.equipement', 'reservation.user');
        return view('inspections.show', compact('inspection'));
    }

    public function edit(Inspection $inspection)
    {
        return view('inspections.edit', $this->formData() + compact('inspection'));
    }

    public function update(InspectionRequest $request, Inspection $inspection)
    {
        $inspection->update($request->validated());
        $this->verifierLitige($inspection);

        return redirect()->route('inspections.show', $inspection)->with('success', 'Inspection modifiée.');
    }

    public function destroy(Inspection $inspection)
    {
        $inspection->delete();
        return redirect()->route('inspections.index')->with('success', 'Inspection supprimée.');
    }

    private function formData(): array
    {
        return [
            'reservations' => Reservation::with('equipement')->latest()->get(),
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
