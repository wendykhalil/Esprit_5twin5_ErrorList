<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReleveEnergieRequest;
use App\Models\ReleveEnergie;
use App\Models\Reservation;
use App\Services\ImpactEcologiqueService;
use App\Services\DetectionAnomalieService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class ReleveEnergieController extends Controller
{
    public function index(): View
    {
        $releves = ReleveEnergie::with(['reservation.equipment', 'reservation.user'])
            ->whereHas('reservation', fn ($query) => $query->where('user_id', auth()->id()))
            ->latest('date_releve')->paginate(10);
        $user = auth()->user();

        // Requête directe (pas via hasManyThrough) : la colonne laravel_through_key casserait le GROUP BY en MySQL strict.
        $valid = ReleveEnergie::whereHas('reservation', fn ($query) => $query->where('user_id', $user->id))
            ->whereIn('statut_verification', ['normal', 'valide']);
        $monthly = (clone $valid)->selectRaw("DATE_FORMAT(date_releve, '%Y-%m') as period, SUM(kwh_estimes) as total")
            ->groupBy('period')->orderBy('period')->get();
        $months = collect(range(5, 0))->map(fn (int $offset) => now()->subMonths($offset)->format('Y-m'));
        $monthlyByPeriod = $monthly->keyBy('period');
        $monthly = $months->map(fn (string $period) => (object) [
            'period' => $period,
            'total' => (float) ($monthlyByPeriod->get($period)->total ?? 0),
        ]);
        $cumulative = [];
        $running = 0.0;
        foreach ($monthly as $row) {
            $running += (float) $row->total;
            $cumulative[] = ['period' => $row->period, 'total' => $running];
        }
        $byCategory = (clone $valid)->join('reservations', 'releves_energie.reservation_id', '=', 'reservations.id')
            ->join('equipment', 'reservations.equipment_id', '=', 'equipment.id')
            ->leftJoin('categories', 'equipment.category_id', '=', 'categories.id')
            ->selectRaw("COALESCE(categories.name, 'Sans catégorie') as category, SUM(releves_energie.kwh_estimes) as total")
            ->groupBy('category')->get();
        $badges = $user->badges()->latest()->get();
        $impactValues = [
            'kwh_total' => (float) (clone $valid)->sum('kwh_estimes'),
            'co2_total' => (float) (clone $valid)->sum('co2_evite_kg'),
            'reservations_terminees' => (int) $user->reservations()->where('statut', 'terminee')->count(),
            'retours_sans_dommage' => (int) $user->inspections()->where('type', 'retour')->where('etat', '!=', 'endommage')->count(),
            'releves_declares' => (int) (clone $valid)->count(),
        ];
        return view('impact.index', [
            'releves' => $releves,
            'totalKwh' => $user->relevesEnergie()->whereIn('statut_verification', ['normal', 'valide'])->sum('kwh_estimes'),
            'totalCo2' => $user->relevesEnergie()->whereIn('statut_verification', ['normal', 'valide'])->sum('co2_evite_kg'),
            'badges' => $badges,
            'monthlyData' => $monthly,
            'cumulativeData' => $cumulative,
            'categoryData' => $byCategory,
            'badgeCatalogue' => \App\Models\Badge::where('actif', true)->orderBy('seuil')->get(),
            'impactFormatter' => \App\Support\ImpactFormatter::class,
            'currentKwh' => (float) (clone $valid)->sum('kwh_estimes'),
            'currentCo2' => (float) (clone $valid)->sum('co2_evite_kg'),
            'impactValues' => $impactValues,
            'co2Thresholds' => \App\Models\Badge::where('actif', true)->where('critere', 'co2_total')->orderBy('seuil')->pluck('seuil'),
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

    public function store(ReleveEnergieRequest $request, ImpactEcologiqueService $service, DetectionAnomalieService $detector): RedirectResponse
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
            return $releve;
        });
        $detector->analyser($releve);

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

    public function update(ReleveEnergieRequest $request, ReleveEnergie $releveEnergie, ImpactEcologiqueService $service, DetectionAnomalieService $detector): RedirectResponse
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
        $detector->analyser($releveEnergie);
        return redirect()->route('impact.show', $releveEnergie)->with('success', 'Relevé modifié avec succès.');
    }

    public function destroy(ReleveEnergie $releveEnergie, ImpactEcologiqueService $service): RedirectResponse
    {
        $this->authorizeOwner($releveEnergie);
        $releveEnergie->delete();
        return redirect()->route('impact.index')->with('success', 'Relevé supprimé avec succès.');
    }

    private function authorizeOwner(ReleveEnergie $releve): void
    {
        $releve->loadMissing('reservation');
        abort_unless($releve->reservation?->user_id === auth()->id(), 403);
    }
}
