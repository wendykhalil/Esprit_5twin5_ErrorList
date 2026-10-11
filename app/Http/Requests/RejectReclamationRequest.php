<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectReclamationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Seul un administrateur peut refuser une réclamation
        return auth()->user()?->isAdmin() === true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'rejected_reason' => [
                'required',
                'string',
                'max:2000',
            ],
            'admin_notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'rejected_reason.required' => 'Le motif du rejet est requis.',
            'rejected_reason.max' => 'Le motif du rejet ne peut pas dépasser 2000 caractères.',
            'admin_notes.max' => 'Les notes administrateur ne peuvent pas dépasser 2000 caractères.',
        ];
    }
}
