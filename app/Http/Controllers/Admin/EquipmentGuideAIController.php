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

        try {
            $raw = $ai->generateUsageGuide($details);

            // Remove optional Markdown JSON fences.
            $raw = trim($raw);

            $raw = preg_replace(
                '/^```(?:json)?\s*|\s*```$/i',
                '',
                $raw
            );

            $guide = json_decode($raw, true);

            // Mistral can insert unescaped line breaks
            // inside JSON string values.
            if (!is_array($guide)) {
                $fixed = $this->escapeJsonControlCharacters($raw);
                $guide = json_decode($fixed, true);
            }

            if (!is_array($guide)) {
                throw new RuntimeException(
                    'AI returned invalid JSON: ' .
                    json_last_error_msg()
                );
            }

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
     * Escape raw control characters found inside JSON strings.
     * Preserve valid JSON structure and existing escape sequences.
     */
    private function escapeJsonControlCharacters(string $json): string
    {
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
