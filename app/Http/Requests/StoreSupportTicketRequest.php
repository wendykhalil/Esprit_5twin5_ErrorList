<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupportTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'min:5', 'max:150'],
            'message' => ['required', 'string', 'min:20', 'max:5000'],
            'priority' => ['nullable', Rule::in(['low', 'normal', 'high'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'subject.required' => 'Le sujet est obligatoire.',
            'subject.min' => 'Le sujet doit contenir au moins :min caractères.',
            'subject.max' => 'Le sujet ne doit pas dépasser :max caractères.',
            'message.required' => 'Le message est obligatoire.',
            'message.min' => 'Décrivez votre demande avec au moins :min caractères.',
            'message.max' => 'Le message ne doit pas dépasser :max caractères.',
            'priority.in' => 'La priorité sélectionnée n\'est pas valide.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'subject' => 'sujet',
            'message' => 'message',
            'priority' => 'priorité',
        ];
    }
}
