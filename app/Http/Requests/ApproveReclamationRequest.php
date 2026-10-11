<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveReclamationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Seul un administrateur peut approuver une réclamation
        return auth()->user()?->isAdmin() === true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'admin_notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'approved_incidents' => [
                'required',
                'array',
                'min:1',
            ],
            'approved_incidents.*' => [
                'integer',
                'exists:incidents,id',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'admin_notes.max' => 'Les notes administrateur ne peuvent pas dépasser 2000 caractères.',
            'approved_incidents.required' => 'Au moins un incident approuvé est requis.',
            'approved_incidents.min' => 'Au moins un incident approuvé est requis.',
            'approved_incidents.*.integer' => 'L\'ID d\'incident doit être un entier.',
            'approved_incidents.*.exists' => 'L\'incident spécifié n\'existe pas.',
        ];
    }
}
