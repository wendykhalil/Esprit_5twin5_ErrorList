<?php

namespace App\Http\Requests\Admin;

use App\Models\Inspection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InspectionAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $inspection = $this->route('inspection');

        return [
            'reservation_id' => ['required', 'exists:reservations,id'],
            'type' => [
                'required',
                Rule::in(Inspection::TYPES),
                Rule::unique('inspections')->where('reservation_id', $this->reservation_id)->ignore($inspection?->id),
            ],
            'etat' => ['required', Rule::in(Inspection::ETATS)],
            'niveau_batterie' => ['nullable', 'integer', 'between:0,100'],
            'observations' => ['nullable', 'string', 'max:1000'],
            'date_inspection' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.unique' => 'Cette réservation a déjà une inspection de ce type.',
            'niveau_batterie.between' => 'Le niveau de batterie doit être compris entre 0 et 100.',
        ];
    }
}