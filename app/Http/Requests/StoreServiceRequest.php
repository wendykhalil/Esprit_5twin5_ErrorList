<?php

namespace App\Http\Requests;

use App\Rules\NoUnsafeCharacters;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreServiceRequest extends FormRequest
{
    public const TITLE_MIN = 3;

    public const TITLE_MAX = 255;

    public const ADDRESS_MIN = 5;

    public const ADDRESS_MAX = 255;

    public const DESCRIPTION_MIN = 30;

    public const DESCRIPTION_MAX = 2000;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (['title', 'address', 'requested_date'] as $field) {
            if (is_string($this->input($field))) {
                $normalized[$field] = trim($this->input($field));
            }
        }

        if (is_string($this->input('description'))) {
            $description = str_replace(["\r\n", "\r"], "\n", $this->input('description'));
            $normalized['description'] = trim($description);
        }

        if ($this->input('equipment_id') === '') {
            $normalized['equipment_id'] = null;
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => [
                'bail',
                'required',
                'string',
                'min:'.self::TITLE_MIN,
                'max:'.self::TITLE_MAX,
                new NoUnsafeCharacters('Veuillez renseigner un titre valide.'),
            ],
            'requested_date' => [
                'bail',
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ],
            'address' => [
                'bail',
                'required',
                'string',
                'min:'.self::ADDRESS_MIN,
                'max:'.self::ADDRESS_MAX,
                new NoUnsafeCharacters('Veuillez renseigner une adresse d\'intervention valide.'),
            ],
            'equipment_id' => [
                'nullable',
                'integer',
                Rule::exists('equipment', 'id')->where(
                    fn ($query) => $query->where('user_id', $this->user()->id)
                ),
            ],
            'description' => [
                'bail',
                'required',
                'string',
                'min:'.self::DESCRIPTION_MIN,
                'max:'.self::DESCRIPTION_MAX,
                new NoUnsafeCharacters('La description contient des caractères non autorisés.', allowNewlines: true),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Veuillez renseigner un titre valide.',
            'title.string' => 'Veuillez renseigner un titre valide.',
            'title.min' => 'Le titre doit contenir au moins :min caractères.',
            'title.max' => 'Le titre ne doit pas dépasser :max caractères.',
            'requested_date.required' => 'Veuillez indiquer une date valide.',
            'requested_date.date_format' => 'Veuillez indiquer une date valide.',
            'requested_date.after_or_equal' => 'La date souhaitée doit être aujourd\'hui ou une date ultérieure.',
            'address.required' => 'Veuillez renseigner une adresse d\'intervention valide.',
            'address.string' => 'Veuillez renseigner une adresse d\'intervention valide.',
            'address.min' => 'Veuillez renseigner une adresse d\'intervention valide.',
            'address.max' => 'L\'adresse ne doit pas dépasser :max caractères.',
            'equipment_id.integer' => 'Veuillez sélectionner un équipement qui vous appartient.',
            'equipment_id.exists' => 'Veuillez sélectionner un équipement qui vous appartient.',
            'description.required' => 'La description détaillée est obligatoire.',
            'description.string' => 'La description détaillée est obligatoire.',
            'description.min' => 'La description doit contenir au moins :min caractères.',
            'description.max' => 'La description ne doit pas dépasser :max caractères.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $allowed = [
                'title',
                'requested_date',
                'address',
                'equipment_id',
                'description',
                '_token',
                '_method',
            ];

            $protected = ['status', 'estimated_price', 'user_id', 'service_provider_id'];
            $unexpected = array_values(array_diff(array_keys($this->all()), $allowed));

            foreach (array_intersect($protected, $unexpected) as $field) {
                $validator->errors()->add($field, 'Ce champ ne peut pas être modifié depuis ce formulaire.');
            }

            if (array_diff($unexpected, $protected) !== []) {
                $validator->errors()->add('form', 'La requête contient des champs non autorisés.');
            }
        });
    }
}
