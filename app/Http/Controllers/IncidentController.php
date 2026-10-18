<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Reservation;
use App\Services\OllamaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class IncidentController extends Controller
{
    /**
     * Generate an incident description using AI (Ollama)
     *
     * POST /incidents/generate-description
     *
     * @param Request $request
     * @param OllamaService $ollamaService
     * @return JsonResponse
     */
    public function generateDescription(Request $request, OllamaService $ollamaService): JsonResponse
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentification requise.',
            ], 401);
        }

        // Validate input
        $validated = $request->validate([
            'reservation_id' => ['required', 'integer', 'exists:reservations,id'],
            'problem_type' => [
                'required',
                'string',
                Rule::in(['endommage', 'manquant', 'malfonctionnant', 'autre']),
            ],
            'problem_title' => ['required', 'string', 'min:3', 'max:255'],
        ], [
            'reservation_id.required' => 'La réservation est requise.',
            'reservation_id.exists' => 'La réservation spécifiée n\'existe pas.',
            'problem_type.required' => 'Le type de problème est requis.',
            'problem_type.in' => 'Type de problème non valide.',
            'problem_title.required' => 'Le titre du problème est requis.',
            'problem_title.min' => 'Le titre doit contenir au moins 3 caractères.',
            'problem_title.max' => 'Le titre ne peut pas dépasser 255 caractères.',
        ]);

        // Verify reservation belongs to user
        $reservation = Reservation::find($validated['reservation_id']);
        if ($reservation->user_id !== Auth::id()) {
            Log::warning('Unauthorized AI generation attempt', [
                'user_id' => Auth::id(),
                'reservation_id' => $validated['reservation_id'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé.',
            ], 403);
        }

        try {
            // Load equipment and reservation details for context
            $equipment = $reservation->equipment;
            $equipmentName = $equipment ? $equipment->name : null;

            // Format dates for the prompt
            $dateDebut = $reservation->date_debut?->format('d/m/Y');
            $dateFin = $reservation->date_fin?->format('d/m/Y');

            // Generate description using Ollama with full context
            $description = $ollamaService->generateIncidentDescription(
                $validated['problem_type'],
                $validated['problem_title'],
                $equipmentName,
                $dateDebut,
                $dateFin,
                $reservation->id
            );

            return response()->json([
                'success' => true,
                'description' => $description,
            ]);
        } catch (\Exception $e) {
            Log::error('AI generation failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            $message = $e->getMessage();

            // Provide user-friendly error messages
            if (str_contains($message, 'connection') || str_contains($message, 'connecter')) {
                $message = 'Impossible de se connecter au service Ollama. Veuillez vérifier qu\'Ollama est en cours d\'exécution sur http://127.0.0.1:11434';
            } elseif (str_contains($message, 'timeout')) {
                $message = 'La génération a dépassé le délai d\'attente. Veuillez réessayer.';
            }

            return response()->json([
                'success' => false,
                'message' => $message,
            ], 500);
        }
    }
}
