<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InspectionAdminRequest;
use App\Models\Inspection;
use App\Models\Reservation;
use Illuminate\Http\Request;

class InspectionController extends Controller
{
    public function index(Request $request)
    {
        $query = Inspection::with('reservation.user', 'reservation.equipment');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($query) use ($search) {
                $query->whereHas('reservation.user', fn ($user) => $user->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('reservation.equipment', fn ($equipment) => $equipment->where('name', 'like', "%{$search}%"));
            });
        }

        $type = $request->input('type', 'Tous');
        if ($type !== 'Tous') {
            $query->where('type', $type);
        }

        $state = $request->input('etat', 'Tous');
        if ($state !== 'Tous') {
            $query->where('etat', $state);
        }

        $inspections = $query->latest('date_inspection')->paginate(10)->withQueryString();

        return view('backend.inspections.index', [
            'inspections' => $inspections,
            'search' => $request->input('search', ''),
            'selectedType' => $type,
            'selectedState' => $state,
            'types' => ['Tous' => 'Tous', 'remise' => 'Remise', 'retour' => 'Retour'],
            'states' => ['Tous' => 'Tous', 'neuf' => 'Neuf', 'bon' => 'Bon', 'use' => 'Usé', 'endommage' => 'Endommagé'],
        ]);
    }

    public function create(Request $request)
    {
        return view('backend.inspections.create', $this->formData() + [
            'reservation_id' => $request->input('reservation_id'),
        ]);
    }

    public function store(InspectionAdminRequest $request)
    {
        $inspection = Inspection::create($request->validated());
        $this->applyDisputeStatus($inspection);

        return redirect()->route('admin.inspections.index')->with('success', 'Inspection créée.');
    }

    public function show(Inspection $inspection)
    {
        $inspection->load('reservation.user', 'reservation.equipment');

        return view('backend.inspections.show', compact('inspection'));
    }

    public function edit(Inspection $inspection)
    {
        return view('backend.inspections.edit', $this->formData() + compact('inspection'));
    }

    public function update(InspectionAdminRequest $request, Inspection $inspection)
    {
        $inspection->update($request->validated());
        $this->applyDisputeStatus($inspection);

        return redirect()->route('admin.inspections.show', $inspection)->with('success', 'Inspection mise à jour.');
    }

    public function destroy(Inspection $inspection)
    {
        $inspection->delete();

        return redirect()->route('admin.inspections.index')->with('success', 'Inspection supprimée.');
    }

    private function formData(): array
    {
        return [
            'reservations' => Reservation::with('equipment')->with('user')->latest()->get(),
            'types' => Inspection::TYPES,
            'states' => Inspection::ETATS,
        ];
    }

    private function applyDisputeStatus(Inspection $inspection): void
    {
        if ($inspection->type === 'retour' && $inspection->etat === 'endommage') {
            $inspection->reservation()->update(['statut' => 'litige']);
        }
    }
}