<?php

namespace App\Http\Requests;

use App\Rules\HourlyRate;
use App\Rules\NoUnsafeCharacters;
use App\Rules\TunisianPhoneNumber;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreServiceProviderRequest extends FormRequest
{
    public const SPECIALTY_MIN = 2;

    public const SPECIALTY_MAX = 255;

    public const LOCATION_MIN = 2;

    public const LOCATION_MAX = 255;

    public const EXPERIENCE_MIN = 0;

    public const EXPERIENCE_MAX = 60;

    public const DESCRIPTION_MIN = 30;

    public const DESCRIPTION_MAX = 2000;

    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        if ($this->isMethod('POST')) {
            return $user->isClient();
        }

        $profile = $this->route('serviceProvider');

        return $profile !== null && $profile->user_id === $user->id;
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (['specialty', 'location'] as $field) {
            if (is_string($this->input($field))) {
                $normalized[$field] = trim($this->input($field));
            }
        }

        if (is_string($this->input('description'))) {
            $description = str_replace(["\r\n", "\r"], "\n", $this->input('description'));
            $normalized['description'] = trim($description);
        }

        if (is_string($this->input('experience_years'))) {
            $normalized['experience_years'] = trim($this->input('experience_years'));
        }

        if (is_string($this->input('phone'))) {
            $phone = trim($this->input('phone'));
            $normalized['phone'] = $phone === '' ? null : (TunisianPhoneNumber::normalize($phone) ?? $phone);
        }

        $rate = $this->input('hourly_rate');

        if (is_string($rate) || is_int($rate) || is_float($rate)) {
            $normalizedRate = HourlyRate::normalize($rate);

            if ($normalizedRate !== null) {
                $normalized['hourly_rate'] = $normalizedRate;
            } elseif (is_string($rate)) {
                $normalized['hourly_rate'] = trim($rate);
            }
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
            'specialty' => [
                'bail',
                'required',
                'string',
                'min:'.self::SPECIALTY_MIN,
                'max:'.self::SPECIALTY_MAX,
                new NoUnsafeCharacters('Veuillez renseigner une spécialité valide.'),
            ],
            'location' => [
                'bail',
                'required',
                'string',
                'min:'.self::LOCATION_MIN,
                'max:'.self::LOCATION_MAX,
                new NoUnsafeCharacters('Veuillez renseigner une localisation valide.'),
            ],
            'experience_years' => [
                'bail',
                'required',
                'integer',
                'between:'.self::EXPERIENCE_MIN.','.self::EXPERIENCE_MAX,
            ],
            'hourly_rate' => ['bail', 'required', new HourlyRate],
            'phone' => ['nullable', 'string', new TunisianPhoneNumber],
            'description' => [
                'bail',
                'required',
                'string',
                'min:'.self::DESCRIPTION_MIN,
                'max:'.self::DESCRIPTION_MAX,
                new NoUnsafeCharacters('La description contient des caractères non autorisés.', allowNewlines: true),
            ],
            'availability' => ['nullable', function (string $attribute, mixed $value, \Closure $fail): void {
                $allowed = [null, '', '1', '0', 1, 0, true, false, 'true', 'false'];

                if (! in_array($value, $allowed, true)) {
                    $fail('La disponibilité indiquée est invalide.');
                }
            }],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'specialty.required' => 'Veuillez renseigner une spécialité valide.',
            'specialty.string' => 'Veuillez renseigner une spécialité valide.',
            'specialty.min' => 'Veuillez renseigner une spécialité valide.',
            'specialty.max' => 'La spécialité ne doit pas dépasser :max caractères.',
            'location.required' => 'Veuillez renseigner une localisation valide.',
            'location.string' => 'Veuillez renseigner une localisation valide.',
            'location.min' => 'Veuillez renseigner une localisation valide.',
            'location.max' => 'La localisation ne doit pas dépasser :max caractères.',
            'experience_years.required' => 'Les années d\'expérience sont obligatoires.',
            'experience_years.integer' => 'Le nombre d\'années d\'expérience doit être un entier compris entre 0 et 60.',
            'experience_years.between' => 'Le nombre d\'années d\'expérience doit être un entier compris entre 0 et 60.',
            'hourly_rate.required' => 'Veuillez saisir un tarif horaire valide, positif et comportant au maximum deux décimales.',
            'phone.string' => 'Le numéro de téléphone professionnel est invalide.',
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
                'specialty',
                'location',
                'experience_years',
                'hourly_rate',
                'phone',
                'description',
                'availability',
                '_token',
                '_method',
            ];

            $protected = ['status', 'user_id', 'id', 'role'];
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
