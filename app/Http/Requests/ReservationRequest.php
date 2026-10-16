<?php

namespace App\Http\Requests;

use App\Models\Equipment;
use App\Models\Reservation;
use App\Services\EquipmentAvailabilityService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class ReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'equipment_id' => ['required', 'exists:equipment,id'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after:date_debut'],
            'compensation_code' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'equipment_id.required' => 'Choisissez un équipement.',
            'date_debut.after_or_equal' => "La date de début ne peut pas être dans le passé.",
            'date_fin.after' => 'La date de fin doit être après la date de début.',
        ];
    }

    /**
     * Handle failed validation for AJAX requests
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        if ($this->expectsJson() || $this->header('X-Requested-With') === 'XMLHttpRequest') {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        parent::failedValidation($validator);
    }

    /**
     * Validations personnalisées après la validation standard.
     * Empêche:
     * - L'auto-réservation (utilisateur réservant son propre équipement)
     * - Les chevauchements de dates
     * - Les transitions de statut invalides lors de modifications
     * - Les durées de location non supportées
     * - Validates compensation codes
     */
    public function after(): array
    {
        return [
            function ($validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                // Validate that start date is not before today (calendar date comparison)
                $startDate = Carbon::parse($this->date_debut)->startOfDay();
                $today = Carbon::now()->startOfDay();
                
                if ($startDate < $today) {
                    $validator->errors()->add(
                        'date_debut',
                        'La date de début ne peut pas être dans le passé.'
                    );
                    return;
                }

                // Rule 0: Validate supported rental duration
                $supported_durations = [1, 3, 7, 14];
                $start = Carbon::parse($this->date_debut);
                $end = Carbon::parse($this->date_fin);
                $days = $start->diffInDays($end);

                // The actual days calculated from dates must be one of the supported values
                // Allow for 1-day variations due to timezone/rounding
                $isValidDuration = collect($supported_durations)->contains(fn($d) => abs($d - $days) <= 1);

                if (!$isValidDuration) {
                    $validator->errors()->add(
                        'date_fin',
                        'La durée de location doit être 1 jour, 3 jours, 1 semaine ou 2 semaines.'
                    );
                    return;
                }

                $equipment = Equipment::find($this->equipment_id);
                $userId = auth()->id();

                // Règle 1: Empêcher l'auto-réservation
                if ($equipment && $equipment->user_id === $userId) {
                    $validator->errors()->add(
                        'equipment_id',
                        'Vous ne pouvez pas réserver votre propre équipement.'
                    );
                    return;
                }

                // Règle 2: Vérifier les chevauchements de dates usando EquipmentAvailabilityService
                // This replaces the inline query and uses the centralized availability logic
                $service = new EquipmentAvailabilityService();
                $excludeReservation = $this->route('reservation');

                if ($service->hasOverlap($equipment, $this->date_debut, $this->date_fin, $excludeReservation)) {
                    $validator->errors()->add(
                        'date_debut',
                        'Cet équipement est déjà réservé sur cette période. Veuillez choisir d\'autres dates.'
                    );
                }

                // Règle 3: Si modification, vérifier que le statut permet de changer les dates
                $reservation = $this->route('reservation');
                if ($reservation && in_array($reservation->statut, ['confirmee', 'en_cours', 'terminee', 'litige'])) {
                    $validator->errors()->add(
                        'date_debut',
                        'Vous ne pouvez pas modifier une réservation ' . str_replace('_', ' ', $reservation->statut) . '.'
                    );
                }

                // Règle 4: Vérifier la validité du code de compensation s'il est fourni
                if ($this->compensation_code) {
                    $this->validateCompensationCode($validator);
                }
            },
        ];
    }

    /**
     * Validate compensation code if provided.
     * Checks: existence, ownership, validity, expiration, usage limits.
     */
    private function validateCompensationCode($validator): void
    {
        $code = \App\Models\CompensationCode::where('code', trim($this->compensation_code))
            ->first();

        if (!$code) {
            $validator->errors()->add(
                'compensation_code',
                'Ce code de compensation n\'existe pas.'
            );
            return;
        }

        // Check ownership: code must belong to current user
        if ($code->user_id !== auth()->id()) {
            $validator->errors()->add(
                'compensation_code',
                'Ce code de compensation ne vous appartient pas.'
            );
            return;
        }

        // Check if code is active
        if (!$code->is_active) {
            $validator->errors()->add(
                'compensation_code',
                'Ce code de compensation a été désactivé.'
            );
            return;
        }

        // Check if code has started (valid_from <= now)
        if (!$code->hasStarted()) {
            $validator->errors()->add(
                'compensation_code',
                'Ce code de compensation n\'est pas encore valide.'
            );
            return;
        }

        // Check if code is expired (valid_until > now)
        if ($code->isExpired()) {
            $validator->errors()->add(
                'compensation_code',
                'Ce code de compensation a expiré.'
            );
            return;
        }

        // Check if code has remaining uses
        if (!$code->hasUsesRemaining()) {
            $validator->errors()->add(
                'compensation_code',
                'Ce code de compensation a déjà été utilisé.'
            );
            return;
        }
    }
}
