<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Equipment;
use App\Models\Incident;
use App\Models\Reclamation;
use App\Events\ReclamationCreated;
use App\Services\OllamaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AIReclamationAssistantController extends Controller
{
    /**
     * GET /reclamations/ai-assistant/reservations
     * Get eligible reservations for the authenticated user
     */
    public function getReservations(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentification requise.',
            ], 401);
        }

        $user = Auth::user();

        // Get eligible reservations (confirmed, in progress, or completed)
        $reservations = Reservation::where('user_id', $user->id)
            ->whereIn('statut', ['confirmee', 'en_cours', 'terminee'])
            ->with('equipment')
            ->orderByDesc('date_fin')
            ->get()
            ->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'label' => $this->formatReservationLabel($reservation),
                    'equipment_id' => $reservation->equipment_id,
                    'equipment_name' => $reservation->equipment->name ?? 'N/A',
                    'date_debut' => $reservation->date_debut?->format('d/m/Y'),
                    'date_fin' => $reservation->date_fin?->format('d/m/Y'),
                    'price_per_day' => $reservation->equipment->price_per_day ?? 0,
                ];
            });

        return response()->json([
            'success' => true,
            'reservations' => $reservations,
        ]);
    }

    /**
     * POST /reclamations/ai-assistant/generate-reclamation-details
     * Generate complete reclamation details (type, title, description) from client explanation
     */
    public function generateReclamationDetails(Request $request, OllamaService $ollamaService): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentification requise.',
            ], 401);
        }

        // Validate input
        $validated = $request->validate([
            'reservation_id' => ['required', 'integer', 'exists:reservations,id'],
            'explanation' => ['required', 'string', 'min:20', 'max:1000'],
        ], [
            'reservation_id.required' => 'La réservation est requise.',
            'reservation_id.exists' => 'La réservation spécifiée n\'existe pas.',
            'explanation.required' => 'Une explication est requise.',
            'explanation.min' => 'L\'explication doit contenir au moins 20 caractères.',
            'explanation.max' => 'L\'explication ne peut pas dépasser 1000 caractères.',
        ]);

        // Verify reservation belongs to user
        $reservation = Reservation::find($validated['reservation_id']);
        if ($reservation->user_id !== Auth::id()) {
            Log::warning('Unauthorized reclamation details generation attempt', [
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

            // Generate reclamation details from client explanation
            $details = $ollamaService->generateReclamationDetails(
                $validated['explanation'],
                $equipmentName,
                $dateDebut,
                $dateFin,
                $reservation->id
            );

            return response()->json([
                'success' => true,
                'type_probleme' => $details['type_probleme'],
                'titre' => $details['titre'],
                'description' => $details['description'],
            ]);
        } catch (\Exception $e) {
            Log::error('Reclamation details generation failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'reservation_id' => $validated['reservation_id'],
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /reclamations/ai-assistant/generate-description
     * Generate an incident description using AI
     */
    public function generateDescription(Request $request, OllamaService $ollamaService): JsonResponse
    {
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
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'reservation_id' => $validated['reservation_id'],
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /reclamations/ai-assistant/upload-evidence
     * Upload evidence image for a draft incident
     */
    public function uploadEvidence(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentification requise.',
            ], 401);
        }

        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ], [
            'image.required' => 'Une image est requise.',
            'image.image' => 'Le fichier doit être une image.',
            'image.mimes' => 'Formats acceptés: JPEG, PNG, WebP.',
            'image.max' => 'L\'image ne peut pas dépasser 5 MB.',
        ]);

        try {
            // Store the image temporarily with a unique name
            $path = $validated['image']->store('incidents', 'public');

            return response()->json([
                'success' => true,
                'path' => $path,
                'url' => asset('storage/' . $path),
            ]);
        } catch (\Exception $e) {
            Log::error('Evidence upload failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du téléchargement de l\'image.',
            ], 500);
        }
    }

    /**
     * POST /reclamations/ai-assistant/submit
     * Submit the reclamation draft as a final reclamation
     */
    public function submitReclamation(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentification requise.',
            ], 401);
        }

        $user = Auth::user();
        $validated = $request->validate([
            'reservation_id' => ['required', 'integer', 'exists:reservations,id'],
            'title' => ['required', 'string', 'max:255'],
            'incidents' => ['required', 'array', 'min:1'],
            'incidents.*.equipment_id' => ['required', 'integer', 'exists:equipment,id'],
            'incidents.*.type_probleme' => [
                'required',
                'string',
                Rule::in(['endommage', 'manquant', 'malfonctionnant', 'autre']),
            ],
            'incidents.*.titre' => ['required', 'string', 'min:3', 'max:255'],
            'incidents.*.description' => ['required', 'string', 'max:2000'],
            'incidents.*.damage_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'incidents.*.attachment' => ['nullable', 'string'], // Path, not a file upload
        ], [
            'reservation_id.required' => 'La réservation est requise.',
            'reservation_id.exists' => 'La réservation n\'existe pas.',
            'title.required' => 'Le titre est requis.',
            'title.max' => 'Le titre ne peut pas dépasser 255 caractères.',
            'incidents.required' => 'Au moins un incident est requis.',
            'incidents.min' => 'Au moins un incident est requis.',
            'incidents.*.type_probleme.in' => 'Type de problème non valide.',
            'incidents.*.titre.required' => 'Le titre de l\'incident est requis.',
            'incidents.*.description.required' => 'La description est requise.',
            'incidents.*.description.max' => 'La description ne peut pas dépasser 2000 caractères.',
        ]);

        // Verify reservation belongs to user
        $reservation = Reservation::findOrFail($validated['reservation_id']);
        if ($reservation->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cette réservation n\'est pas la vôtre.',
            ], 403);
        }

        // Verify each incident's equipment matches reservation equipment
        $reservationEquipmentId = (int) $reservation->equipment_id;
        foreach ($validated['incidents'] as $incidentData) {
            $incidentEquipmentId = (int) $incidentData['equipment_id'];
            if ($incidentEquipmentId !== $reservationEquipmentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'L\'équipement de l\'incident n\'appartient pas à la réservation.',
                ], 403);
            }
        }

        try {
            DB::beginTransaction();

            // Create reclamation
            $reclamation = Reclamation::create([
                'reservation_id' => $validated['reservation_id'],
                'user_id' => $user->id,
                'title' => $validated['title'],
                'description' => null,
                'status' => 'en_attente',
            ]);

            // Create incidents
            foreach ($validated['incidents'] as $incidentData) {
                $equipment = Equipment::findOrFail($incidentData['equipment_id']);

                $proposedRefundAmount = $this->calculateProposedRefund(
                    $incidentData['type_probleme'],
                    $equipment->price_per_day
                );

                $proposedRefundPercentage = match ($incidentData['type_probleme']) {
                    'endommage' => 30.0,
                    'manquant' => 20.0,
                    default => 0.0,
                };

                Incident::create([
                    'reclamation_id' => $reclamation->id,
                    'equipment_id' => $incidentData['equipment_id'],
                    'type_probleme' => $incidentData['type_probleme'],
                    'titre' => $incidentData['titre'],
                    'description' => $incidentData['description'],
                    'damage_percentage' => $incidentData['damage_percentage'] ?? null,
                    'proposed_refund_percentage' => $proposedRefundPercentage,
                    'proposed_refund_amount' => $proposedRefundAmount,
                    'attachment' => $incidentData['attachment'] ?? null,
                ]);
            }

            DB::commit();

            // Dispatch event - wrap in try-catch to handle broadcast errors
            try {
                ReclamationCreated::dispatch($reclamation);
            } catch (\Exception $broadcastError) {
                // Log broadcast error but don't fail the submission
                Log::warning('Broadcast error for reclamation creation', [
                    'reclamation_id' => $reclamation->id,
                    'error' => $broadcastError->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'reclamation_id' => $reclamation->id,
                'message' => 'Réclamation créée avec succès.',
                'redirect' => route('reclamations.show', $reclamation->id),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Reclamation submission failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'reservation_id' => $validated['reservation_id'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la réclamation.',
            ], 500);
        }
    }

    /**
     * Helper: Calculate proposed refund based on problem type and equipment price
     */
    private function calculateProposedRefund(string $problemType, float $equipmentPrice): float
    {
        return match ($problemType) {
            'endommage' => $equipmentPrice * 0.30,
            'manquant' => $equipmentPrice * 0.20,
            default => 0.0,
        };
    }

    /**
     * Helper: Format reservation label for display
     */
    private function formatReservationLabel(Reservation $reservation): string
    {
        $equipment = $reservation->equipment->name ?? 'N/A';
        $dates = sprintf(
            '%s au %s',
            $reservation->date_debut?->format('d/m/Y') ?? 'N/A',
            $reservation->date_fin?->format('d/m/Y') ?? 'N/A'
        );

        return "#{$reservation->id} - {$equipment} ({$dates})";
    }
}
