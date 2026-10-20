<?php

namespace App\Http\Requests;

use App\Models\SupportTicket;
use Illuminate\Foundation\Http\FormRequest;

class ReopenSupportTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('supportTicket');

        return $ticket instanceof SupportTicket
            && $this->user() !== null
            && $this->user()->can('reopen', $ticket);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'motif' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'motif.required' => 'Indiquez pourquoi le problème n\'est pas résolu.',
            'motif.min' => 'Le motif doit contenir au moins :min caractères.',
            'motif.max' => 'Le motif ne doit pas dépasser :max caractères.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('motif') && is_string($this->input('motif'))) {
            $this->merge([
                'motif' => trim($this->input('motif')),
            ]);
        }
    }
};
