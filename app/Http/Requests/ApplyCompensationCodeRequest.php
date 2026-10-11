<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApplyCompensationCodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'type' => [
                'required',
                Rule::in(['percentage', 'fixed']),
            ],
            'value' => [
                'required',
                'numeric',
                'min:0.01',
                function ($attribute, $value, $fail) {
                    if ($this->input('type') === 'percentage' && $value > 100) {
                        $fail('Le pourcentage ne peut pas dépasser 100%.');
                    }
                },
            ],
            'days_valid' => [
                'required',
                'integer',
                'min:1',
                'max:365',
            ],
            'max_uses' => [
                'required',
                'integer',
                'min:1',
            ],
            'admin_notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Le type de réduction est requis.',
            'type.in' => 'Le type de réduction doit être "pourcentage" ou "montant fixe".',
            'value.required' => 'La valeur est requise.',
            'value.numeric' => 'La valeur doit être un nombre.',
            'value.min' => 'La valeur doit être au minimum 0.01.',
            'days_valid.required' => 'Le nombre de jours de validité est requis.',
            'days_valid.integer' => 'Le nombre de jours doit être un nombre entier.',
            'days_valid.min' => 'Le nombre de jours doit être au minimum 1.',
            'days_valid.max' => 'Le nombre de jours ne peut pas dépasser 365.',
            'max_uses.required' => 'Le nombre maximum d\'utilisations est requis.',
            'max_uses.integer' => 'Le nombre d\'utilisations doit être un nombre entier.',
            'max_uses.min' => 'Le nombre d\'utilisations doit être au minimum 1.',
            'admin_notes.max' => 'Les notes ne peuvent pas dépasser 1000 caractères.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure numeric fields are treated as numbers
        if ($this->has('value')) {
            $this->merge([
                'value' => (float) $this->input('value'),
            ]);
        }

        if ($this->has('max_uses')) {
            $this->merge([
                'max_uses' => (int) $this->input('max_uses'),
            ]);
        }

        if ($this->has('days_valid')) {
            $this->merge([
                'days_valid' => (int) $this->input('days_valid'),
            ]);
        }
    }
}
