<?php

namespace App\Http\Requests\Admin;

use App\Models\Reservation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReservationAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'equipment_id' => ['required', 'exists:equipment,id'],
            'date_debut' => ['required', 'date', 'after_or_equal:today'],
            'date_fin' => ['required', 'date', 'after:date_debut'],
            'statut' => ['required', Rule::in(Reservation::STATUTS)],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Choisissez un utilisateur.',
            'equipment_id.required' => 'Choisissez un équipement.',
            'date_debut.after_or_equal' => 'La date de début ne peut pas être dans le passé.',
            'date_fin.after' => 'La date de fin doit être après la date de début.',
            'statut.required' => 'Choisissez un statut.',
            'statut.in' => 'Le statut sélectionné est invalide.',
        ];
    }

    /**
     * Validations personnalisées après la validation standard.
     * Empêche:
     * - L'auto-réservation (utilisateur réservant son propre équipement)
     * - Les chevauchements de dates (sauf si modifie une réservation existante)
     */
    public function after(): array
    {
        return [function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            // Règle 1: Empêcher l'auto-réservation
            $equipment = \App\Models\Equipment::find($this->equipment_id);
            if ($equipment && $equipment->user_id === $this->user_id) {
                $validator->errors()->add(
                    'equipment_id',
                    'Un utilisateur ne peut pas réserver son propre équipement.'
                );
                return;
            }

            // Règle 2: Vérifier les chevauchements de dates
            // Exclut: annulee, terminee, refusee
            $conflict = Reservation::where('equipment_id', $this->equipment_id)
                ->whereNotIn('statut', ['annulee', 'terminee', 'refusee'])
                ->when($this->route('reservation'), fn ($query, $reservation) => $query->where('id', '!=', $reservation->id))
                ->where('date_debut', '<', $this->date_fin)
                ->where('date_fin', '>', $this->date_debut)
                ->exists();

            if ($conflict) {
                $validator->errors()->add('date_debut', 'Cet équipement est déjà réservé sur cette période.');
            }
        }];
    }
}
