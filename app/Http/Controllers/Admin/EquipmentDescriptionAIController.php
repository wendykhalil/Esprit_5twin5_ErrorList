<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SolarShareAIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use Throwable;

class EquipmentDescriptionAIController extends Controller
{
    public function generate(
        Request $request,
        SolarShareAIService $ai
    ): JsonResponse {

        $validated = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'category' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'power' => 'nullable|numeric|min:0',
            'capacity' => 'nullable|numeric|min:0',
            'condition' => 'nullable|in:excellent,good,used',
        ]);

        try {
            // Ask Mistral to generate structured information.
            $raw = trim(
                $ai->generateEquipmentDescription($validated)
            );

            // Remove optional Markdown fences.
            $raw = trim(
                preg_replace(
                    '/^```(?:json)?\s*|\s*```$/i',
                    '',
                    $raw
                )
            );

            $result = json_decode($raw, true);

            if (!is_array($result)) {
                throw new RuntimeException(
                    'AI returned invalid JSON: ' . json_last_error_msg()
                );
            }

            // Validate the AI response.
            $validator = Validator::make($result, [
                'description' => [
                    'required',
                    'string',
                    'min:10',
                    'max:1000',
                ],
                'power' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:999999',
                ],
                'capacity' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:999999',
                ],
            ]);

            if ($validator->fails()) {
                throw new RuntimeException(
                    'AI response validation failed: ' .
                    $validator->errors()->first()
                );
            }

            /*
             * Only accept numerical values that are explicitly
             * present in the submitted equipment information.
             * This avoids trusting unsupported AI guesses.
             */
            $power = $this->verifiedValue(
                $validated,
                'power',
                $result['power'] ?? null,
                'W',
                'kW'
            );

            $capacity = $this->verifiedValue(
                $validated,
                'capacity',
                $result['capacity'] ?? null,
                'Wh',
                'kWh'
            );

            return response()->json([
                'success' => true,
                'description' => trim($result['description']),
                'power' => $power,
                'capacity' => $capacity,
            ]);

        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Impossible de générer les informations. Vérifiez Ollama et réessayez.',
            ], 503);
        }
    }

    /**
     * Accept existing form values, or extract a clearly
     * labelled technical value from the equipment name.
     *
     * Never invent missing specifications.
     */
    private function verifiedValue(
        array $equipment,
        string $field,
        mixed $aiValue,
        string $unit,
        string $largerUnit
    ): ?float {

        // A value already entered by the admin takes priority.
        if (
            isset($equipment[$field]) &&
            is_numeric($equipment[$field])
        ) {
            return (float) $equipment[$field];
        }

        $name = (string) ($equipment['name'] ?? '');

        // Match kW before W, and kWh before Wh.
        $pattern = '/(?<![\d\w])(\d+(?:[.,]\d+)?)\s*(' .
            preg_quote($largerUnit, '/') . '|' .
            preg_quote($unit, '/') .
            ')(?![a-zA-Z])/i';

        if (!preg_match($pattern, $name, $matches)) {
            return null;
        }

        $value = (float) str_replace(',', '.', $matches[1]);
        $matchedUnit = strtolower($matches[2]);

        if ($matchedUnit === strtolower($largerUnit)) {
            $value *= 1000;
        }

        if ($value < 0 || $value > 999999) {
            return null;
        }

        // Do not rely on a guessed number from the AI.
        return $value;
    }
}
