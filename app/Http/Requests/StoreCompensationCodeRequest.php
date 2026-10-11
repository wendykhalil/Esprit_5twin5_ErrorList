<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompensationCodeRequest extends FormRequest
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
            'valid_from' => [
                'required',
                'date_format:Y-m-d H:i',
            ],
            'valid_until' => [
                'required',
                'date_format:Y-m-d H:i',
                'after:valid_from',
            ],
            'max_uses' => [
                'required',
                'integer',
                'min:1',
            ],
            'user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],
            'description' => [
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
            'valid_from.required' => 'La date de début est requise.',
            'valid_from.date_format' => 'La date de début doit être au format YYYY-MM-DD HH:MM.',
            'valid_until.required' => 'La date d\'expiration est requise.',
            'valid_until.date_format' => 'La date d\'expiration doit être au format YYYY-MM-DD HH:MM.',
            'valid_until.after' => 'La date d\'expiration doit être après la date de début.',
            'max_uses.required' => 'Le nombre maximum d\'utilisations est requis.',
            'max_uses.integer' => 'Le nombre d\'utilisations doit être un nombre entier.',
            'max_uses.min' => 'Le nombre d\'utilisations doit être au minimum 1.',
            'user_id.exists' => 'L\'utilisateur spécifié n\'existe pas.',
            'description.max' => 'La description ne peut pas dépasser 1000 caractères.',
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

        // Null user_id if empty
        if ($this->input('user_id') === '' || $this->input('user_id') === null) {
            $this->merge([
                'user_id' => null,
            ]);
        }

        // Convert datetime-local format (Y-m-d\TH:i) to expected format (Y-m-d H:i)
        if ($this->has('valid_from')) {
            $validFrom = $this->input('valid_from');
            if ($validFrom && strpos($validFrom, 'T') !== false) {
                $this->merge([
                    'valid_from' => str_replace('T', ' ', $validFrom),
                ]);
            }
        }

        if ($this->has('valid_until')) {
            $validUntil = $this->input('valid_until');
            if ($validUntil && strpos($validUntil, 'T') !== false) {
                $this->merge([
                    'valid_until' => str_replace('T', ' ', $validUntil),
                ]);
            }
        }
    }
}
