<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReleveEnergieRequest;
use App\Models\ReleveEnergie;
use App\Models\Reservation;
use App\Services\ImpactEcologiqueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReleveEnergieController extends Controller
{
    public function index(): View
    {
        $releves = ReleveEnergie::with(['reservation.equipment', 'reservation.user'])
            ->whereHas('reservation', fn ($query) => $query->where('user_id', auth()->id()))
            ->latest('date_releve')->paginate(10);
        $user = auth()->user();

        return view('impact.index', [
            'releves' => $releves,
            'totalKwh' => $user->relevesEnergie()->sum('kwh_estimes'),
            'totalCo2' => $user->relevesEnergie()->sum('co2_evite_kg'),
            'badges' => $user->badges()->latest()->get(),
        ]);
    }

    public function create(): View
    {
        $reservations = Reservation::with('equipment')
            ->where('user_id', auth()->id())
            ->where('statut', 'terminee')
            ->whereDoesntHave('releveEnergie')
            ->latest('date_fin')->get();

        return view('impact.create', compact('reservations'));
    }

    public function store(ReleveEnergieRequest $request, ImpactEcologiqueService $service): RedirectResponse
    {
        $data = $request->validated();
        $reservation = Reservation::with('equipment')
            ->where('user_id', auth()->id())->findOrFail($data['reservation_id']);
        abort_unless($reservation->statut === 'terminee' && !$reservation->releveEnergie()->exists(), 422);

        $power = (float) ($reservation->equipment?->power ?? 0);
        abort_if($power <= 0, 422, 'La puissance de cet équipement est indisponible.');
        $calculated = $service->calculate($power, (float) $data['heures_utilisation']);

        $releve = DB::transaction(function () use ($data, $reservation, $power, $calculated, $service) {
            $releve = ReleveEnergie::create([
                'reservation_id' => $reservation->id,
                'puissance_watts' => $power,
                'heures_utilisation' => $data['heures_utilisation'],
                ...$calculated,
                'date_releve' => $data['date_releve'],
                'observations' => $data['observations'] ?? null,
            ]);
            $service->syncBadgesFor($releve);
            return $releve;
        });

        return redirect()->route('impact.show', $releve)->with('success', 'Relevé d’impact créé avec succès.');
    }

    public function show(ReleveEnergie $releveEnergie): View
    {
        $this->authorizeOwner($releveEnergie);
        $releveEnergie->load(['reservation.equipment', 'reservation.user']);
        return view('impact.show', ['releve' => $releveEnergie]);
    }

    public function edit(ReleveEnergie $releveEnergie): View
    {
        $this->authorizeOwner($releveEnergie);
        $releveEnergie->load(['reservation.equipment']);
        return view('impact.edit', ['releve' => $releveEnergie]);
    }

    public function update(ReleveEnergieRequest $request, ReleveEnergie $releveEnergie, ImpactEcologiqueService $service): RedirectResponse
    {
        $this->authorizeOwner($releveEnergie);
        $data = $request->validated();
        $releveEnergie->load('reservation.equipment');
        abort_unless($releveEnergie->reservation?->statut === 'terminee', 422);
        abort_unless((int) $data['reservation_id'] === $releveEnergie->reservation_id, 422);
        $power = (float) ($releveEnergie->reservation->equipment?->power ?? $releveEnergie->puissance_watts ?? 0);
        abort_if($power <= 0, 422, 'La puissance de cet équipement est indisponible.');
        $releveEnergie->update([
            'puissance_watts' => $releveEnergie->puissance_watts ?? $power,
            'heures_utilisation' => $data['heures_utilisation'],
            ...$service->calculate($power, (float) $data['heures_utilisation']),
            'date_releve' => $data['date_releve'],
            'observations' => $data['observations'] ?? null,
        ]);
        $service->syncBadgesFor($releveEnergie);

        return redirect()->route('impact.show', $releveEnergie)->with('success', 'Relevé modifié avec succès.');
    }

    public function destroy(ReleveEnergie $releveEnergie, ImpactEcologiqueService $service): RedirectResponse
    {
        $this->authorizeOwner($releveEnergie);
        $releveEnergie->load('reservation.user');
        $user = $releveEnergie->reservation->user;
        $releveEnergie->delete();
        $service->syncBadges($user);

        return redirect()->route('impact.index')->with('success', 'Relevé supprimé avec succès.');
    }

    private function authorizeOwner(ReleveEnergie $releve): void
    {
        $releve->loadMissing('reservation');
        abort_unless($releve->reservation?->user_id === auth()->id(), 403);
    }
}
