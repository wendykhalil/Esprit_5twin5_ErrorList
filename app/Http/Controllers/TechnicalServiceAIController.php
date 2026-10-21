<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\ServiceProvider;
use App\Services\TechnicalServiceAIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TechnicalServiceAIController extends Controller
{
    public function __construct(
        private readonly TechnicalServiceAIService $aiService
    ) {}

    /**
     * Génère un pré-diagnostic IA indicatif et l'enregistre en session serveur avec empreinte (fingerprint).
     */
    public function diagnose(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'service_provider_id' => ['required', 'integer', 'exists:service_providers,id'],
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['required', 'string', 'min:20', 'max:2000'],
            'equipment_id' => ['nullable', 'integer', 'exists:equipment,id'],
        ], [
            'title.required' => 'Veuillez saisir un titre d\'intervention.',
            'title.min' => 'Le titre doit comporter au moins :min caractères.',
            'description.required' => 'Veuillez décrire la panne constatée.',
            'description.min' => 'La description doit comporter au moins :min caractères pour permettre l\'analyse.',
        ]);

        // 1. Prestataire : doit être approuvé ET disponible
        $serviceProvider = ServiceProvider::where('id', $validated['service_provider_id'])
            ->where('status', 'approved')
            ->where('availability', true)
            ->first();

        if (!$serviceProvider) {
            return response()->json([
                'success' => false,
                'message' => 'Ce prestataire n\'est pas disponible pour de nouvelles interventions.',
            ], 422);
        }

        // 2. Exclusion : interdiction pour un prestataire de demander un diagnostic sur son propre profil
        if ($serviceProvider->user_id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas demander un diagnostic pour votre propre profil prestataire.',
            ], 403);
        }

        // 3. Équipement : échec explicite en 403 si l'équipement appartient à un autre utilisateur
        $equipmentName = null;
        $equipmentCategory = null;
        $equipmentId = !empty($validated['equipment_id']) ? (int) $validated['equipment_id'] : null;

        if ($equipmentId !== null) {
            $equipment = Equipment::with('category')->find($equipmentId);

            if (!$equipment || $equipment->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à utiliser cet équipement.',
                ], 403);
            }

            $equipmentName = $equipment->name;
            $equipmentCategory = $equipment->category?->name;
        }

        // 4. Inférence IA / Heuristique sécurisée (en TND)
        $diagnosis = $this->aiService->analyze(
            title: $validated['title'],
            description: $validated['description'],
            equipmentName: $equipmentName,
            equipmentCategory: $equipmentCategory
        );

        // 5. Calcul de l'empreinte cryptographique des données analysées
        $fingerprint = TechnicalServiceAIService::computeFingerprint(
            providerId: $serviceProvider->id,
            equipmentId: $equipmentId,
            title: $validated['title'],
            description: $validated['description']
        );

        // 6. Stash en session serveur (empreinte + diagnostic)
        $sessionKey = 'ts_ai_pre_diagnosis_' . $serviceProvider->id;
        session()->put($sessionKey, [
            'fingerprint' => $fingerprint,
            'diagnosis' => array_merge($diagnosis, [
                'generated_at' => now()->toIso8601String(),
            ]),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pré-diagnostic technique généré avec succès.',
            'diagnosis' => $diagnosis,
        ]);
    }
}
