<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Services\SolarShareAIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use Throwable;

class EquipmentGuideAIController extends Controller
{
    /**
     * Generate an AI equipment usage guide.
     */
    public function generate(
        Request $request,
        SolarShareAIService $ai
    ): JsonResponse {

        $validated = $request->validate([
            'equipment_id' => [
                'required',
                'integer',
                'exists:equipment,id',
            ],
        ]);

        $equipment = Equipment::findOrFail(
            $validated['equipment_id']
        );

        $details = $equipment->only([
    'name',
    'description',
    'brand',
    'power',
    'capacity',
    'condition',
]);

// Allow longer execution for local AI guide generation.
@set_time_limit(240);

try {

            // Generate the guide using Ollama / Mistral.
            $raw = $ai->generateUsageGuide($details);

            // Decode the AI response.
            $guide = $this->decodeAIJson($raw);

            // Convert AI arrays into strings when necessary.
            $guide = $this->normalizeGuide($guide);

            // Validate the final guide structure.
            $validator = Validator::make($guide, [
                'title' => [
                    'required',
                    'string',
                    'min:5',
                    'max:150',
                ],

                'usage_context' => [
                    'required',
                    'string',
                    'min:5',
                    'max:255',
                ],

                'instructions' => [
                    'required',
                    'string',
                    'min:30',
                    'max:5000',
                ],

                'safety_precautions' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],

                'difficulty_level' => [
                    'required',
                    'in:beginner,intermediate,advanced',
                ],
            ]);

            if ($validator->fails()) {
                throw new RuntimeException(
                    'AI output failed guide validation: ' .
                    $validator->errors()->first()
                );
            }

            return response()->json([
                'success' => true,
                'guide' => $validator->validated(),
            ]);

        } catch (Throwable $e) {

            report($e);

            return response()->json([
                'success' => false,
                'message' =>
                    'Impossible de générer le guide. Vérifiez Ollama et réessayez.',
            ], 503);
        }
    }

    /**
     * Normalize the fields returned by Mistral.
     *
     * Some models return instructions as an array
     * instead of the string expected by Laravel.
     */
    private function normalizeGuide(array $guide): array
    {
        if (
            isset($guide['instructions']) &&
            is_array($guide['instructions'])
        ) {
            $guide['instructions'] = $this->arrayToText(
                $guide['instructions'],
                true
            );
        }

        if (
            isset($guide['safety_precautions']) &&
            is_array($guide['safety_precautions'])
        ) {
            $guide['safety_precautions'] = $this->arrayToText(
                $guide['safety_precautions'],
                false
            );
        }

        if (
            isset($guide['usage_context']) &&
            is_array($guide['usage_context'])
        ) {
            $guide['usage_context'] = $this->arrayToText(
                $guide['usage_context'],
                false
            );
        }

        if (
            isset($guide['title']) &&
            is_array($guide['title'])
        ) {
            $guide['title'] = $this->arrayToText(
                $guide['title'],
                false
            );
        }

        // Normalize difficulty labels if Mistral
        // returns a French equivalent.
        if (
            isset($guide['difficulty_level']) &&
            is_string($guide['difficulty_level'])
        ) {
            $difficulty = strtolower(
                trim($guide['difficulty_level'])
            );

            $levels = [
                'débutant' => 'beginner',
                'debutant' => 'beginner',
                'facile' => 'beginner',
                'beginner' => 'beginner',

                'intermédiaire' => 'intermediate',
                'intermediaire' => 'intermediate',
                'moyen' => 'intermediate',
                'intermediate' => 'intermediate',

                'avancé' => 'advanced',
                'avance' => 'advanced',
                'difficile' => 'advanced',
                'advanced' => 'advanced',
            ];

            $guide['difficulty_level'] =
                $levels[$difficulty] ?? $difficulty;
        }

        return $guide;
    }

    /**
     * Convert a structured array into readable text.
     */
    private function arrayToText(
        array $items,
        bool $numbered = false
    ): string {

        $lines = [];

        foreach ($items as $key => $item) {

            if (is_string($item) || is_numeric($item)) {

                $text = trim((string) $item);

            } elseif (is_array($item)) {

                $parts = [];

                foreach ($item as $value) {
                    if (is_scalar($value)) {
                        $part = trim((string) $value);

                        if ($part !== '') {
                            $parts[] = $part;
                        }
                    }
                }

                $text = implode(' - ', $parts);

            } else {
                continue;
            }

            if ($text === '') {
                continue;
            }

            if ($numbered) {
                $lines[] = (count($lines) + 1) .
                    '. ' . $text;
            } else {
                $lines[] = $text;
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Decode JSON returned by the AI.
     */
    private function decodeAIJson(string $raw): array
    {
        $raw = trim($raw);

        // Remove optional Markdown JSON fences.
        $raw = preg_replace(
            '/^```(?:json)?\s*|\s*```$/i',
            '',
            $raw
        );

        $raw = trim($raw);

        $decoded = json_decode($raw, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        // Repair unescaped control characters.
        $fixed = $this->escapeJsonControlCharacters($raw);

        $decoded = json_decode($fixed, true);

        if (!is_array($decoded)) {
            throw new RuntimeException(
                'AI returned invalid JSON: ' .
                json_last_error_msg()
            );
        }

        return $decoded;
    }

    /**
     * Escape control characters inside JSON strings.
     */
    private function escapeJsonControlCharacters(
        string $json
    ): string {

        $result = '';
        $insideString = false;
        $escaped = false;

        $length = strlen($json);

        for ($i = 0; $i < $length; $i++) {

            $char = $json[$i];

            if ($escaped) {
                $result .= $char;
                $escaped = false;
                continue;
            }

            if ($insideString && $char === '\\') {
                $result .= $char;
                $escaped = true;
                continue;
            }

            if ($char === '"') {
                $insideString = !$insideString;
                $result .= $char;
                continue;
            }

            if ($insideString) {

                switch ($char) {

                    case "\n":
                        $result .= '\\n';
                        continue 2;

                    case "\r":
                        $result .= '\\r';
                        continue 2;

                    case "\t":
                        $result .= '\\t';
                        continue 2;
                }

                if (ord($char) < 32) {

                    $result .= sprintf(
                        '\\u%04x',
                        ord($char)
                    );

                    continue;
                }
            }

            $result .= $char;
        }

        return $result;
    }
}
