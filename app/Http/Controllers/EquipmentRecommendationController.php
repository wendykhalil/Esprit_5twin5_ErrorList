<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Services\SolarShareAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class EquipmentRecommendationController extends Controller
{
    public function index()
    {
        return view('frontend.equipments.recommendations');
    }

    public function recommend(Request $request, SolarShareAIService $ai)
    {
        $validated = $request->validate([
            'needs' => ['required', 'string', 'min:10', 'max:1000'],
            'budget' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'location' => ['nullable', 'string', 'max:100'],
        ]);

        $query = Equipment::query()
            ->with('category')
            ->where('status', 'active')
            ->where('availability', true);

        if (isset($validated['budget'])) {
            $query->where('price_per_day', '<=', $validated['budget']);
        }

        if (!empty($validated['location'])) {
            $query->where('location', 'like', '%' .
                $validated['location'] . '%');
        }

        $candidates = $query
            ->orderBy('id')
            ->limit(30)
            ->get();

        if ($candidates->isEmpty()) {
            return back()
                ->withInput()
                ->with('recommendation_error',
                    'Aucun équipement disponible ne correspond aux filtres.');
        }

        $catalogue = $candidates->map(function ($equipment) {
            return [
                'id' => $equipment->id,
                'name' => $equipment->name,
                'category' => $equipment->category?->name,
                'description' => mb_substr(
                    (string) $equipment->description, 0, 350
                ),
                'brand' => $equipment->brand,
                'power_w' => $equipment->power,
                'capacity_wh' => $equipment->capacity,
                'price_per_day_tnd' => $equipment->price_per_day,
                'location' => $equipment->location,
                'condition' => $equipment->condition,
            ];
        })->all();

        try {
            $raw = $ai->recommendEquipment(
                $validated['needs'],
                $catalogue
            );

            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

            if (!is_array($data) ||
                !isset($data['recommendations']) ||
                !is_array($data['recommendations'])) {
                throw new \RuntimeException('Invalid AI response.');
            }

            $recommendations = collect();

            foreach ($data['recommendations'] as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $id = filter_var(
                    $item['id'] ?? null,
                    FILTER_VALIDATE_INT
                );

                if (!$id || $recommendations->has($id)) {
                    continue;
                }

                $equipment = $candidates->firstWhere('id', $id);

                if (!$equipment) {
                    continue;
                }

                $reason = $item['reason'] ?? '';

                if (!is_string($reason) || trim($reason) === '') {
                    continue;
                }

                $recommendations->put($id, [
                    'equipment' => $equipment,
                    'reason' => mb_substr(trim($reason), 0, 500),
                ]);

                if ($recommendations->count() >= 3) {
                    break;
                }
            }

            if ($recommendations->isEmpty()) {
                throw new \RuntimeException(
                    'No valid equipment recommendations.'
                );
            }

            return view('frontend.equipments.recommendations', [
                'recommendations' => $recommendations->values(),
                'needs' => $validated['needs'],
                'budget' => $validated['budget'] ?? null,
                'location' => $validated['location'] ?? null,
            ]);

        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('recommendation_error',
                    'L’assistant IA est temporairement indisponible. Réessayez plus tard.');
        }
    }
}
