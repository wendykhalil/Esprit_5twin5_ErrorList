<?php

namespace App\Services;

use App\Models\Equipment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class EstimationProductionService
{
    public function estimate(Equipment $equipment, float $hours): array
    {
        $city = $equipment->location ?: config('solarshare.weather.default_city');
        $coordinates = config('solarshare.weather.cities.'.$city, config('solarshare.weather.cities.'.config('solarshare.weather.default_city')));
        $factor = $this->weatherFactor($equipment, $coordinates);
        $kwh = (float) $equipment->power * $hours / 1000 * $factor;
        return ['kwh_estimes' => $kwh, 'co2_evite_kg' => $kwh * (float) config('solarshare.co2_kg_par_kwh'), 'facteur_meteo' => $factor];
    }

    private function weatherFactor(Equipment $equipment, array $coordinates): float
    {
        $type = strtolower((string) ($equipment->category?->name ?? $equipment->name));
        $isWind = str_contains($type, 'vent') || str_contains($type, 'éol');
        $key = 'solarshare.weather.'.md5(json_encode([$coordinates, $isWind, now()->format('Y-m-d-H-').floor((int) now()->format('i') / 30)]));
        return Cache::remember($key, now()->addMinutes(30), function () use ($coordinates, $isWind) {
            try {
                $params = $isWind ? 'windspeed_10m' : 'shortwave_radiation';
                $response = Http::timeout(4)->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => $coordinates['latitude'], 'longitude' => $coordinates['longitude'],
                    'hourly' => $params, 'forecast_days' => 1,
                ])->throw()->json();
                $values = array_filter(data_get($response, 'hourly.'.$params, []), 'is_numeric');
                $average = count($values) ? array_sum($values) / count($values) : 0;
                return $isWind ? max(0.4, min(1.4, $average / 12)) : max(0.4, min(1.4, $average / 250));
            } catch (ConnectionException|RequestException|\JsonException) {
                return 1.0;
            }
        });
    }
}
