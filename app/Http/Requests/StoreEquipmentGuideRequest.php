<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEquipmentGuideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'equipment_id' => [
                'required',
                'integer',
                Rule::exists('equipment', 'id'),
            ],

            'title' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],

            'usage_context' => [
                'required',
                'string',
                'min:5',
                'max:255',
            ],

            'instructions' => [
                'required',
                'string',
                'min:20',
            ],

            'safety_precautions' => [
                'nullable',
                'string',
            ],

            'difficulty_level' => [
                'required',
                Rule::in([
                    'beginner',
                    'intermediate',
                    'advanced',
                ]),
            ],

            'video_url' => [
                'nullable',
                'url',
                'max:255',
            ],

            'status' => [
                'required',
                Rule::in([
                    'draft',
                    'pending',
                    'published',
                ]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'equipment_id.required' =>
                'Veuillez sélectionner un équipement.',
            'equipment_id.integer' =>
                'L’équipement sélectionné est invalide.',
            'equipment_id.exists' =>
                'L’équipement sélectionné n’existe pas.',

            'title.required' =>
                'Le titre du guide est obligatoire.',
            'title.string' =>
                'Le titre doit être un texte.',
            'title.min' =>
                'Le titre doit contenir au moins :min caractères.',
            'title.max' =>
                'Le titre ne doit pas dépasser :max caractères.',

            'usage_context.required' =>
                'Le contexte d’utilisation est obligatoire.',
            'usage_context.string' =>
                'Le contexte doit être un texte.',
            'usage_context.min' =>
                'Le contexte doit contenir au moins :min caractères.',
            'usage_context.max' =>
                'Le contexte ne doit pas dépasser :max caractères.',

            'instructions.required' =>
                'Les instructions sont obligatoires.',
            'instructions.string' =>
                'Les instructions doivent être un texte.',
            'instructions.min' =>
                'Les instructions doivent contenir au moins :min caractères.',

            'safety_precautions.string' =>
                'Les précautions de sécurité doivent être un texte.',

            'difficulty_level.required' =>
                'Veuillez sélectionner un niveau de difficulté.',
            'difficulty_level.in' =>
                'Le niveau de difficulté sélectionné est invalide.',

            'video_url.url' =>
                'Veuillez saisir une URL vidéo valide.',
            'video_url.max' =>
                'L’URL ne doit pas dépasser :max caractères.',

            'status.required' =>
                'Veuillez sélectionner un statut.',
            'status.in' =>
                'Le statut sélectionné est invalide.',
        ];
    }

    public function attributes(): array
    {
        return [
            'equipment_id' => 'équipement',
            'title' => 'titre',
            'usage_context' => 'contexte d’utilisation',
            'instructions' => 'instructions',
            'safety_precautions' => 'précautions de sécurité',
            'difficulty_level' => 'niveau de difficulté',
            'video_url' => 'URL vidéo',
            'status' => 'statut',
        ];
    }
}
