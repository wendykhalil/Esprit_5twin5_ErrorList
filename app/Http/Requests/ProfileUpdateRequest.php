<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire.',
            'name.string' => 'Le nom doit être un texte.',
            'name.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.string' => 'L\'adresse email doit être un texte.',
            'email.lowercase' => 'L\'adresse email doit être en minuscules.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.max' => 'L\'adresse email ne doit pas dépasser 255 caractères.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'phone.string' => 'Le numéro de téléphone doit être un texte.',
            'phone.max' => 'Le numéro de téléphone ne doit pas dépasser 30 caractères.',
            'address.string' => 'L\'adresse doit être un texte.',
            'address.max' => 'L\'adresse ne doit pas dépasser 255 caractères.',
            'city.string' => 'La ville doit être un texte.',
            'city.max' => 'La ville ne doit pas dépasser 100 caractères.',
            'country.string' => 'Le pays doit être un texte.',
            'country.max' => 'Le pays ne doit pas dépasser 100 caractères.',
            'bio.string' => 'La bio doit être un texte.',
            'bio.max' => 'La bio ne doit pas dépasser 1000 caractères.',
            'profile_photo.image' => 'La photo doit être une image.',
            'profile_photo.mimes' => 'La photo doit être en format JPEG, JPG, PNG ou WebP.',
            'profile_photo.max' => 'La photo ne doit pas dépasser 2 Mo.',
        ];
    }
}
