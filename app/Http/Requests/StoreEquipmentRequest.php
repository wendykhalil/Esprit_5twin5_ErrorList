<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return self::equipmentRules();
    }

    /**
     * @return array<int, list<string>>
     */
    public static function wizardStepFields(): array
    {
        return [
            1 => ['name', 'category_id', 'description', 'brand', 'power', 'capacity', 'condition'],
            2 => ['location'],
            3 => ['image'],
            4 => ['price_per_day', 'status', 'availability'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function rulesForWizardStep(int $step): array
    {
        $fields = self::wizardStepFields()[$step] ?? [];

        if ($fields === []) {
            return [];
        }

        return array_intersect_key(self::equipmentRules(), array_flip($fields));
    }

    /**
     * @return array<string, mixed>
     */
    public static function equipmentRules(): array
    {
        return [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['required', 'string', 'min:10', 'max:1000'],
            'brand' => ['nullable', 'string', 'max:255'],
            'power' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'capacity' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'condition' => ['required', Rule::in(['excellent', 'good', 'used'])],
            'price_per_day' => ['required', 'numeric', 'min:0', 'max:99999'],
            'location' => ['required', 'string', 'max:255'],
            'availability' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Veuillez sélectionner une catégorie.',
            'category_id.exists' => 'La catégorie choisie n\'est pas valide.',
            'name.required' => 'Le nom de l\'équipement est obligatoire.',
            'name.min' => 'Le nom doit contenir au moins :min caractères.',
            'name.max' => 'Le nom ne doit pas dépasser :max caractères.',
            'description.required' => 'La description est obligatoire.',
            'description.min' => 'La description doit contenir au moins :min caractères.',
            'description.max' => 'La description ne doit pas dépasser :max caractères.',
            'brand.max' => 'La marque ne doit pas dépasser :max caractères.',
            'power.numeric' => 'La puissance doit être un nombre.',
            'power.min' => 'La puissance ne peut pas être négative.',
            'power.max' => 'La puissance indiquée semble trop élevée.',
            'capacity.numeric' => 'La capacité doit être un nombre.',
            'capacity.min' => 'La capacité ne peut pas être négative.',
            'capacity.max' => 'La capacité indiquée semble trop élevée.',
            'condition.required' => 'Veuillez indiquer l\'état de l\'équipement.',
            'condition.in' => 'L\'état sélectionné n\'est pas valide.',
            'price_per_day.required' => 'Le prix par jour est obligatoire.',
            'price_per_day.numeric' => 'Le prix doit être un nombre.',
            'price_per_day.min' => 'Le prix ne peut pas être négatif.',
            'price_per_day.max' => 'Le prix par jour semble trop élevé.',
            'location.required' => 'Veuillez sélectionner une ville.',
            'location.max' => 'La ville ne doit pas dépasser :max caractères.',
            'status.required' => 'Veuillez choisir un statut de publication.',
            'status.in' => 'Le statut sélectionné n\'est pas valide.',
            'image.image' => 'Le fichier doit être une image.',
            'image.mimes' => 'Formats acceptés : JPG, JPEG, PNG ou WEBP.',
            'image.max' => 'L\'image ne doit pas dépasser 2 Mo.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'catégorie',
            'name' => 'nom',
            'description' => 'description',
            'brand' => 'marque',
            'power' => 'puissance',
            'capacity' => 'capacité',
            'condition' => 'état',
            'price_per_day' => 'prix par jour',
            'location' => 'ville',
            'status' => 'statut',
            'image' => 'photo',
        ];
    }
}
