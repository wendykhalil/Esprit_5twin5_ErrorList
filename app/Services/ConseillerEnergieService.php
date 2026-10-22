<?php

namespace App\Services;

use App\Models\Equipment;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class ConseillerEnergieService
{
    public function estDisponible(): bool
    {
        return app(OllamaAvailability::class)->isReady(
            OllamaSettings::baseUrl(),
            OllamaSettings::model(),
            2
        );
    }

    public function recommander(string $besoin): array
    {
        $catalogue = $this->catalogue($besoin);
        if (!$catalogue) {
            return ['recommandations' => [], 'message' => 'Aucun équipement disponible ne correspond à votre besoin.'];
        }
        $system = 'Réponds UNIQUEMENT avec un JSON valide en français, sans markdown, sous cette forme exacte : {"recommandations":[{"equipment_id":1,"justification":"Pour votre besoin, cet équipement est adapté.","kwh_estimes":2.5},{"equipment_id":2,"justification":"Une seconde option pertinente.","kwh_estimes":3.0}]}. Donne 1 à 3 recommandations. Les equipment_id doivent venir EXCLUSIVEMENT de la liste fournie. Ne fixe jamais de prix et ne remplace pas les calculs de l’application.';
        if (! app()->runningUnitTests() && ! $this->estDisponible()) {
            return ['recommandations' => [], 'message' => 'Le conseiller IA n’a pas pu produire une réponse fiable.'];
        }
        $user = 'Besoin : '.mb_substr($besoin, 0, 500)."\nCatalogue disponible (maximum 15) : ".json_encode($catalogue, JSON_UNESCAPED_UNICODE);
        $payload = array_merge(OllamaSettings::requestFields(['temperature' => 0.2]), [
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
            'format' => 'json',
        ]);
        for ($attempt = 0; $attempt < 2; $attempt++) {
            try {
                $response = Http::timeout(OllamaSettings::timeout())->post(OllamaSettings::baseUrl().'/api/chat', $payload)->throw()->json();
                $decoded = json_decode((string) data_get($response, 'message.content'), true, 512, JSON_THROW_ON_ERROR);
                $valid = $this->validateRecommendations($decoded, $catalogue);
                if ($valid) {
                    return ['recommandations' => $valid];
                }
            } catch (\JsonException|ConnectionException|\Illuminate\Http\Client\RequestException) {
                if ($attempt === 1) {
                    break;
                }
            }
        }
        return ['recommandations' => [], 'message' => 'Le conseiller IA n’a pas pu produire une réponse fiable.'];
    }

    public function bilan(float $kwh, float $co2, int $badges): ?string
    {
        $system = 'Répondez uniquement en français, en vous adressant à la personne avec "Vous". Écrivez 2 à 3 phrases, sans emoji et sans markdown. Parlez uniquement d’énergie PRODUITE et de CO2 ÉVITÉ. N’utilisez jamais le mot "consommation". Utilisez uniquement les chiffres fournis, n’inventez rien et n’écrivez jamais à la première personne.';
        if (! app()->runningUnitTests() && ! $this->estDisponible()) {
            return $this->fallbackBilan($kwh, $co2, $badges);
        }
        $prompt = 'Chiffres disponibles : '.json_encode(['energie_produite_kwh' => $kwh, 'co2_evite_kg' => $co2, 'badges' => $badges], JSON_UNESCAPED_UNICODE);
        try {
            $response = Http::timeout(OllamaSettings::timeout())->post(OllamaSettings::baseUrl().'/api/chat', array_merge(
                OllamaSettings::requestFields(['temperature' => 0.2]),
                [
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]
            ))->throw()->json();
            $text = trim((string) data_get($response, 'message.content'));
            return $this->validBilan($text, $kwh, $co2, $badges) ? $text : $this->fallbackBilan($kwh, $co2, $badges);
        } catch (ConnectionException|\Illuminate\Http\Client\RequestException) {
            return $this->fallbackBilan($kwh, $co2, $badges);
        }
    }

    private function validBilan(string $text, float $kwh, float $co2, int $badges): bool
    {
        $normalized = mb_strtolower($text);
        if ($text === '' || preg_match('/\bje\s|j[’\']|consommation/u', $normalized)) {
            return false;
        }
        $sentences = preg_split('/(?<=[.!?])\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);
        if (count($sentences) < 2 || count($sentences) > 3) {
            return false;
        }
        foreach ([$kwh, $co2, $badges] as $number) {
            $formatted = rtrim(rtrim(number_format($number, 4, '.', ''), '0'), '.');
            if ($formatted !== '0' && !str_contains(str_replace(',', '.', $text), $formatted)) {
                return false;
            }
        }
        return true;
    }

    private function fallbackBilan(float $kwh, float $co2, int $badges): string
    {
        return 'Vous avez produit '.number_format($kwh, 2, ',', ' ').' kWh et évité '.number_format($co2, 2, ',', ' ').' kg de CO2. Vous avez obtenu '.$badges.' badge'.($badges > 1 ? 's' : '').' grâce à cet impact mesuré. Chaque relevé validé contribue à faire progresser votre parcours.';
    }

    private function catalogue(string $besoin): array
    {
        $terms = preg_split('/\s+/u', mb_strtolower($besoin), -1, PREG_SPLIT_NO_EMPTY);
        $query = Equipment::query()->with('category')->where('availability', true);
        $equipments = $query->get();
        $scored = $equipments->map(function (Equipment $equipment) use ($terms) {
            $text = mb_strtolower($equipment->name.' '.($equipment->category?->name ?? '').' '.$equipment->power);
            return ['score' => count(array_filter($terms, fn ($term) => mb_strlen($term) > 2 && str_contains($text, $term))), 'equipment' => $equipment];
        })->sortByDesc('score')->take(15);
        return $scored->map(fn ($item) => [
            'id' => $item['equipment']->id, 'nom' => $item['equipment']->name,
            'categorie' => $item['equipment']->category?->name, 'puissance' => (float) $item['equipment']->power,
            'prix_par_jour' => (float) $item['equipment']->price_per_day,
        ])->values()->all();
    }

    private function validateRecommendations(mixed $decoded, array $catalogue): array
    {
        $allowed = collect($catalogue)->pluck('id')->map(fn ($id) => (int) $id)->all();
        return collect(data_get($decoded, 'recommandations', []))->filter(function ($item) use ($allowed) {
            return is_array($item) && isset($item['equipment_id'], $item['justification'])
                && in_array((int) $item['equipment_id'], $allowed, true)
                && is_string($item['justification']) && $item['justification'] !== '';
        })->take(3)->map(fn ($item) => [
            'equipment_id' => (int) $item['equipment_id'],
            'justification' => $item['justification'],
        ])->values()->all();
    }
}
