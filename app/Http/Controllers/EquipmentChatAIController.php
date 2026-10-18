<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Services\SolarShareAIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class EquipmentChatAIController extends Controller
{
    public function chat(
        Request $request,
        Equipment $equipment,
        SolarShareAIService $ai
    ): JsonResponse {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:1000'],
            'history' => ['sometimes', 'array', 'max:6'],
            'history.*.role' => [
                'required',
                'in:user,assistant',
            ],
            'history.*.content' => [
                'required',
                'string',
                'max:1200',
            ],
        ]);

        $details = $equipment->only([
            'name',
            'description',
            'brand',
            'power',
            'capacity',
            'condition',
        ]);

        // Only approved/published guides should be
        // passed to the chatbot.
        // Confirm the real publication status first.
$guides = $equipment->equipmentGuides()
    ->where('status', 'published')
    ->get([
        'title',
        'usage_context',
        'instructions',
        'safety_precautions',
        'difficulty_level',
    ])
    ->toArray();
        try {
            $answer = $ai->chatAboutEquipment(
                $details,
                $validated['question'],
                $validated['history'] ?? [],
                $guides
            );

            return response()->json([
                'success' => true,
                'answer' => $answer,
            ]);

        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'L’assistant IA est temporairement indisponible.',
            ], 503);
        }
    }
}
