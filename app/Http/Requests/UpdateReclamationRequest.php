<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReclamationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization is checked in the controller via policy
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'required',
                'string',
                'max:5000',
            ],
            // Existing incidents can be modified or removed via 'incidents_to_remove'
            'incidents_to_remove' => [
                'nullable',
                'array',
            ],
            'incidents_to_remove.*' => [
                'integer',
                'exists:incidents,id',
            ],
            // New incidents to add
            'new_incidents' => [
                'nullable',
                'array',
            ],
            'new_incidents.*.equipment_id' => [
                'required',
                'integer',
                'exists:equipment,id',
            ],
            'new_incidents.*.type_probleme' => [
                'required',
                'string',
                'in:endommage,manquant,malfonctionnant,autre',
            ],
            'new_incidents.*.description' => [
                'required',
                'string',
                'max:2000',
            ],
            'new_incidents.*.damage_percentage' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],
            'new_incidents.*.image' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:5120', // 5 MB
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Le titre est requis.',
            'title.max' => 'Le titre ne peut pas dépasser 255 caractères.',
            'description.required' => 'La description est requise.',
            'description.max' => 'La description ne peut pas dépasser 5000 caractères.',
            'new_incidents.*.equipment_id.required' => 'L\'équipement est requis pour chaque incident.',
            'new_incidents.*.equipment_id.exists' => 'L\'équipement spécifié n\'existe pas.',
            'new_incidents.*.type_probleme.required' => 'Le type de problème est requis.',
            'new_incidents.*.type_probleme.in' => 'Le type de problème n\'est pas valide.',
            'new_incidents.*.description.required' => 'La description de l\'incident est requise.',
            'new_incidents.*.description.max' => 'La description ne peut pas dépasser 2000 caractères.',
            'new_incidents.*.damage_percentage.numeric' => 'Le pourcentage de dommages doit être un nombre.',
            'new_incidents.*.damage_percentage.min' => 'Le pourcentage de dommages ne peut pas être négatif.',
            'new_incidents.*.damage_percentage.max' => 'Le pourcentage de dommages ne peut pas dépasser 100.',
            'new_incidents.*.image.image' => 'Le fichier doit être une image valide.',
            'new_incidents.*.image.mimes' => 'Le format d\'image n\'est pas accepté. Utilisez JPG, PNG ou WebP.',
            'new_incidents.*.image.max' => 'L\'image ne peut pas dépasser 5 MB.',
            'incidents_to_remove.*.exists' => 'Un ou plusieurs incidents n\'ont pas pu être trouvés.',
        ];
    }
}
