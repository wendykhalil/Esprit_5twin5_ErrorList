<?php

namespace App\Http\Controllers;

use App\Services\ConseillerEnergieService;
use App\Services\EstimationProductionService;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ConseillerEnergieController extends Controller
{
    public function index(ConseillerEnergieService $service)
    {
        return view('impact.conseiller', ['disponible' => $service->estDisponible()]);
    }

    public function recommend(Request $request, ConseillerEnergieService $service, EstimationProductionService $estimator)
    {
        set_time_limit((int) config('solarshare.ollama.timeout') + 5);
        $data = $request->validate(['besoin' => ['required', 'string', 'max:500']]);
        $key = 'conseiller:'.$request->user()->id;
        abort_if(RateLimiter::tooManyAttempts($key, 10), 429, 'Limite de 10 requêtes par heure atteinte.');
        RateLimiter::hit($key, 3600);
        $result = $service->recommander($data['besoin']);
        if (!empty($result['recommandations'])) {
            $equipmentIds = collect($result['recommandations'])->pluck('equipment_id');
            $equipments = Equipment::with('category')->whereIn('id', $equipmentIds)->get()->keyBy('id');
            $result['recommandations'] = collect($result['recommandations'])->map(function (array $recommendation) use ($equipments, $estimator): array {
                $equipment = $equipments->get($recommendation['equipment_id']);
                $calculated = $estimator->estimate($equipment, 8);
                return $recommendation + [
                    'name' => $equipment->name,
                    'kwh_estimes' => $calculated['kwh_estimes'],
                    'kwh_label' => \App\Support\ImpactFormatter::energy($calculated['kwh_estimes']).' attendus pour 8 h (selon la météo)',
                ];
            })->all();
        }
        return response()->json($result);
    }

    public function bilan(Request $request, ConseillerEnergieService $service)
    {
        $key = 'conseiller:'.$request->user()->id;
        abort_if(RateLimiter::tooManyAttempts($key, 10), 429, 'Limite de 10 requêtes par heure atteinte.');
        RateLimiter::hit($key, 3600);
        $user = $request->user();
        $query = $user->relevesEnergie()->whereIn('statut_verification', ['normal', 'valide']);
        $text = $service->bilan((float) $query->sum('kwh_estimes'), (float) $query->sum('co2_evite_kg'), $user->badges()->count());
        return response()->json(['bilan' => $text, 'indisponible' => $text === null]);
    }
}
