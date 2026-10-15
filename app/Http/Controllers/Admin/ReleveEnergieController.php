<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReleveEnergie;
use App\Models\Reservation;
use App\Services\ImpactEcologiqueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReleveEnergieController extends Controller
{
    public function index(Request $request)
    {
        $query = ReleveEnergie::with(['reservation.equipment', 'reservation.user']);
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->whereHas('reservation.user', fn ($q) => $q->where('name', 'like', '%'.$search.'%'))
                ->orWhereHas('reservation.equipment', fn ($q) => $q->where('name', 'like', '%'.$search.'%'));
        }
        return view('backend.impact.index', [
            'releves' => $query->latest('date_releve')->paginate(15)->withQueryString(),
            'search' => $request->input('search'),
        ]);
    }

    public function create()
    {
        $reservations = Reservation::with(['user', 'equipment'])
            ->where('statut', 'terminee')->whereDoesntHave('releveEnergie')->latest('date_fin')->get();
        return view('backend.impact.create', compact('reservations'));
    }

    public function store(Request $request, ImpactEcologiqueService $service)
    {
        $data = $this->validated($request);
        $reservation = Reservation::with('equipment')->findOrFail($data['reservation_id']);
        $this->assertEligible($reservation);
        $power = $this->power($reservation);
        $releve = DB::transaction(function () use ($data, $reservation, $power, $service) {
            $releve = ReleveEnergie::create([
                'reservation_id' => $reservation->id,
                'puissance_watts' => $power,
                'heures_utilisation' => $data['heures_utilisation'],
                ...$service->calculate($power, $data['heures_utilisation']),
                'date_releve' => $data['date_releve'],
                'observations' => $data['observations'] ?? null,
            ]);
            $service->syncBadgesFor($releve);
            return $releve;
        });
        return redirect()->route('admin.impact.show', $releve)->with('success', 'Relevé créé avec succès.');
    }

    public function show(ReleveEnergie $releveEnergie)
    {
        return view('backend.impact.show', ['releve' => $releveEnergie->load(['reservation.equipment', 'reservation.user'])]);
    }

    public function edit(ReleveEnergie $releveEnergie)
    {
        return view('backend.impact.edit', ['releve' => $releveEnergie->load(['reservation.equipment', 'reservation.user'])]);
    }

    public function update(Request $request, ReleveEnergie $releveEnergie, ImpactEcologiqueService $service)
    {
        $data = $this->validated($request);
        abort_unless((int) $data['reservation_id'] === $releveEnergie->reservation_id, 422);
        $releveEnergie->load('reservation.equipment');
        abort_unless($releveEnergie->reservation->statut === 'terminee', 422);
        $power = $this->power($releveEnergie->reservation);
        abort_if($power <= 0, 422, 'La puissance de cet équipement est indisponible.');
        $releveEnergie->update([
            'puissance_watts' => $releveEnergie->puissance_watts ?? $power,
            'heures_utilisation' => $data['heures_utilisation'],
            ...$service->calculate($power, $data['heures_utilisation']),
            'date_releve' => $data['date_releve'],
            'observations' => $data['observations'] ?? null,
        ]);
        $service->syncBadgesFor($releveEnergie);
        return redirect()->route('admin.impact.show', $releveEnergie)->with('success', 'Relevé modifié avec succès.');
    }

    public function destroy(ReleveEnergie $releveEnergie, ImpactEcologiqueService $service)
    {
        $releveEnergie->load('reservation.user');
        $user = $releveEnergie->reservation->user;
        $releveEnergie->delete();
        $service->syncBadges($user);
        return redirect()->route('admin.impact.index')->with('success', 'Relevé supprimé avec succès.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'reservation_id' => ['required', 'exists:reservations,id'],
            'heures_utilisation' => ['required', 'numeric', 'min:0.1'],
            'date_releve' => ['required', 'date', 'before_or_equal:today'],
            'observations' => ['nullable', 'string', 'max:2000'],
        ]);
        $reservation = Reservation::findOrFail($data['reservation_id']);
        $max = max(1, $reservation->date_debut->diffInDays($reservation->date_fin) * 24);
        abort_if((float) $data['heures_utilisation'] > $max, 422, 'La durée dépasse celle de la réservation.');
        return $data;
    }

    private function assertEligible(Reservation $reservation): void
    {
        abort_unless($reservation->statut === 'terminee' && !$reservation->releveEnergie()->exists(), 422);
        abort_if($this->power($reservation) <= 0, 422, 'La puissance de cet équipement est indisponible.');
    }

    private function power(Reservation $reservation): float
    {
        return (float) ($reservation->equipment?->power ?? 0);
    }
}
