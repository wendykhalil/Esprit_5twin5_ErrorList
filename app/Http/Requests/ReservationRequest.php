<?php

namespace App\Http\Requests;

use App\Models\Equipment;
use App\Models\Reservation;
use Illuminate\Foundation\Http\FormRequest;

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
            'date_debut' => ['required', 'date', 'after_or_equal:today'],
            'date_fin' => ['required', 'date', 'after:date_debut'],
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
     * Validations personnalisées après la validation standard.
     * Empêche:
     * - L'auto-réservation (utilisateur réservant son propre équipement)
     * - Les chevauchements de dates
     * - Les transitions de statut invalides lors de modifications
     */
    public function after(): array
    {
        return [
            function ($validator) {
                if ($validator->errors()->isNotEmpty()) {
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

                // Règle 2: Vérifier les chevauchements de dates
                // Exclut: annulee, terminee, refusee
                // Inclut: en_attente, confirmee, en_cours, litige
                $conflit = Reservation::where('equipment_id', $this->equipment_id)
                    ->whereNotIn('statut', ['annulee', 'terminee', 'refusee'])
                    ->when($this->route('reservation'), fn ($q, $r) => $q->where('id', '!=', $r->id))
                    ->where('date_debut', '<', $this->date_fin)
                    ->where('date_fin', '>', $this->date_debut)
                    ->exists();

                if ($conflit) {
                    $validator->errors()->add(
                        'date_debut',
                        'Cet équipement est déjà réservé sur cette période.'
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
            },
        ];
    }
}
