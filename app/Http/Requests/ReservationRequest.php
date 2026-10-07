<?php

namespace App\Http\Requests;

use App\Models\Reservation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'equipement_id' => ['required', 'exists:equipements,id'],
            'date_debut' => ['required', 'date', 'after_or_equal:today'],
            'date_fin' => ['required', 'date', 'after:date_debut'],
            'statut' => ['required', Rule::in(Reservation::STATUTS)],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Choisissez un utilisateur.',
            'equipement_id.required' => 'Choisissez un équipement.',
            'date_debut.after_or_equal' => "La date de début ne peut pas être dans le passé.",
            'date_fin.after' => 'La date de fin doit être après la date de début.',
        ];
    }

    // Valeur ajoutée : empêcher deux réservations qui se chevauchent sur le même équipement
    public function after(): array
    {
        return [
            function ($validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }
                $conflit = Reservation::where('equipement_id', $this->equipement_id)
                    ->whereNotIn('statut', ['annulee', 'terminee'])
                    ->when($this->route('reservation'), fn ($q, $r) => $q->where('id', '!=', $r->id))
                    ->where('date_debut', '<', $this->date_fin)
                    ->where('date_fin', '>', $this->date_debut)
                    ->exists();

                if ($conflit) {
                    $validator->errors()->add('date_debut', 'Cet équipement est déjà réservé sur cette période.');
                }
            },
        ];
    }
}
