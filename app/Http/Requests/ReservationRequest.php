<?php

namespace App\Http\Requests;

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

    // Valeur ajoutée : empêcher deux réservations qui se chevauchent sur le même équipement
    public function after(): array
    {
        return [
            function ($validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }
                $conflit = Reservation::where('equipment_id', $this->equipment_id)
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
