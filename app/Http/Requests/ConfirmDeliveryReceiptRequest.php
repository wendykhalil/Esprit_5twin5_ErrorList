<?php

namespace App\Http\Requests;

use App\Models\Delivery;
use Illuminate\Foundation\Http\FormRequest;

class ConfirmDeliveryReceiptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Only the client who owns the delivery can confirm receipt.
     */
    public function authorize(): bool
    {
        $delivery = $this->route('delivery');
        
        // Ensure user is authenticated and is the delivery's client
        return auth()->check() && auth()->id() === $delivery->user_id;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'condition_notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'condition_notes.max' => 'Les notes ne peuvent pas dépasser 500 caractères.',
        ];
    }

    /**
     * Custom validation logic.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $delivery = $this->route('delivery');

            // Delivery must be in 'prete' status to confirm receipt
            if ($delivery->status !== 'prete') {
                $validator->errors()->add(
                    'delivery',
                    'Cette livraison n\'est pas prête pour confirmation. Statut actuel: ' . $delivery->status
                );
            }
        });
    }
}
