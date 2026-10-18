<?php

namespace App\Http\Requests;

use App\Models\Reclamation;
use Illuminate\Foundation\Http\FormRequest;

class StoreReclamationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // L'utilisateur authentifié peut créer une réclamation pour sa propre réservation
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'reservation_id' => [
                'required',
                'integer',
                'exists:reservations,id',
            ],
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'incidents' => [
                'required',
                'array',
                'min:1',
            ],
            'incidents.*.equipment_id' => [
                'required',
                'integer',
                'exists:equipment,id',
            ],
            'incidents.*.type_probleme' => [
                'required',
                'string',
                'in:' . implode(',', [
                    'endommage',
                    'manquant',
                    'malfonctionnant',
                    'autre',
                ]),
            ],
            'incidents.*.titre' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],
            'incidents.*.description' => [
                'required',
                'string',
                'max:2000',
            ],
            'incidents.*.damage_percentage' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],
            'incidents.*.image' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:5120', // 5 MB in kilobytes
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'reservation_id.required' => 'La réservation est requise.',
            'reservation_id.exists' => 'La réservation spécifiée n\'existe pas.',
            'title.required' => 'Le titre est requis.',
            'title.max' => 'Le titre ne peut pas dépasser 255 caractères.',
            'incidents.required' => 'Au moins un incident est requis.',
            'incidents.min' => 'Au moins un incident est requis.',
            'incidents.*.equipment_id.required' => 'L\'équipement est requis pour chaque incident.',
            'incidents.*.equipment_id.exists' => 'L\'équipement spécifié n\'existe pas.',
            'incidents.*.type_probleme.required' => 'Le type de problème est requis.',
            'incidents.*.type_probleme.in' => 'Le type de problème n\'est pas valide.',
            'incidents.*.titre.required' => 'Le titre du problème est requis pour chaque incident.',
            'incidents.*.titre.min' => 'Le titre du problème doit contenir au moins 3 caractères.',
            'incidents.*.titre.max' => 'Le titre du problème ne peut pas dépasser 255 caractères.',
            'incidents.*.description.required' => 'La description de l\'incident est requise.',
            'incidents.*.description.max' => 'La description de l\'incident ne peut pas dépasser 2000 caractères.',
            'incidents.*.damage_percentage.numeric' => 'Le pourcentage de dommages doit être un nombre.',
            'incidents.*.damage_percentage.min' => 'Le pourcentage de dommages ne peut pas être négatif.',
            'incidents.*.damage_percentage.max' => 'Le pourcentage de dommages ne peut pas dépasser 100.',
            'incidents.*.image.image' => 'Le fichier doit être une image valide.',
            'incidents.*.image.mimes' => 'Le format d\'image n\'est pas accepté. Utilisez JPG, PNG ou WebP.',
            'incidents.*.image.max' => 'L\'image ne peut pas dépasser 5 MB.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => auth()->id(),
        ]);
    }
}
