<?php

namespace App\Http\Requests;

use App\Models\Reservation;
use Illuminate\Foundation\Http\FormRequest;

class ReleveEnergieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $reservationId = $this->input('reservation_id') ?: $this->route('releve_energie')?->reservation_id;
        $reservation = $reservationId ? Reservation::find($reservationId) : null;
        $maxHours = $reservation
            ? max(1, $reservation->date_debut->diffInDays($reservation->date_fin) * 24)
            : 1;

        return [
            'reservation_id' => ['required', 'integer', 'exists:reservations,id'],
            'heures_utilisation' => ['required', 'numeric', 'min:0.1', 'max:'.$maxHours],
            'date_releve' => ['required', 'date', 'before_or_equal:today'],
            'observations' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'reservation_id.required' => 'La réservation est obligatoire.',
            'heures_utilisation.required' => 'Les heures d’utilisation sont obligatoires.',
            'heures_utilisation.min' => 'La durée doit être d’au moins 0,1 heure.',
            'heures_utilisation.max' => 'La durée dépasse celle de la réservation.',
            'date_releve.required' => 'La date du relevé est obligatoire.',
            'date_releve.before_or_equal' => 'La date du relevé ne peut pas être dans le futur.',
        ];
    }
}
