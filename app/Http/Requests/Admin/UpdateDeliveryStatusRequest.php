<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeliveryStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                'in:a_preparer,prete,remise_au_client,retour_recu,terminee',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Le statut est obligatoire.',
            'status.in' => 'Le statut sélectionné est invalide.',
            'notes.max' => 'Les notes ne peuvent pas dépasser 500 caractères.',
        ];
    }
}
