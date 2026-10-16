<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Concerns\RejectsUnexpectedFields;
use App\Models\User;
use App\Rules\PasswordWithinHashLimit;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    use RejectsUnexpectedFields;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        if (is_string($this->input('name'))) {
            $normalized['name'] = trim($this->input('name'));
        }

        if (is_string($this->input('username'))) {
            $normalized['username'] = Str::lower(
                trim($this->input('username'))
            );
        }

        if (is_string($this->input('phone'))) {
            $normalized['phone'] = trim($this->input('phone'));
        }

        if (is_string($this->input('email'))) {
            $normalized['email'] = Str::lower(
                trim($this->input('email'))
            );
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
            'name' => [
                'bail',
                'required',
                'string',
                'max:255',
            ],

            'username' => [
                'bail',
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-z0-9_]+$/',
                'unique:users,username',
            ],

            'phone' => [
                'bail',
                'required',
                'string',
                'max:20',
                'regex:/^\+?[0-9 ]{8,20}$/',
            ],

            'email' => [
                'bail',
                'required',
                'string',
                'email:rfc',
                'max:255',
                'unique:'.User::class,
            ],

            'password' => [
                'bail',
                'required',
                'string',
                Password::defaults(),
                new PasswordWithinHashLimit,
            ],

            'password_confirmation' => [
                'bail',
                'required',
                'string',
                'same:password',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Name
            'name.required' => 'Le nom est obligatoire.',
            'name.string' => 'Le nom doit être une chaîne de caractères.',
            'name.max' => 'Le nom ne doit pas dépasser :max caractères.',

            // Username
            'username.required' => 'Le nom d\'utilisateur est obligatoire.',
            'username.string' => 'Le nom d\'utilisateur doit être une chaîne de caractères.',
            'username.min' => 'Le nom d\'utilisateur doit contenir au moins :min caractères.',
            'username.max' => 'Le nom d\'utilisateur ne doit pas dépasser :max caractères.',
            'username.regex' => 'Le nom d\'utilisateur doit contenir uniquement des lettres minuscules, des chiffres et des underscores (_).',
            'username.unique' => 'Ce nom d\'utilisateur est déjà utilisé.',

            // Phone
            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'phone.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'phone.max' => 'Le numéro de téléphone ne doit pas dépasser :max caractères.',
            'phone.regex' => 'Le numéro de téléphone doit contenir entre 8 et 20 caractères (chiffres, espaces et éventuellement + au début).',

            // Email
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.string' => 'L\'adresse e-mail doit être une chaîne de caractères.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
            'email.max' => 'L\'adresse e-mail ne doit pas dépasser :max caractères.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',

            // Password
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.string' => 'Le mot de passe doit être une chaîne de caractères.',
            'password.min' => 'Le mot de passe doit contenir au moins :min caractères.',

            // Password confirmation
            'password_confirmation.required' => 'La confirmation du mot de passe est obligatoire.',
            'password_confirmation.string' => 'La confirmation du mot de passe doit être une chaîne de caractères.',
            'password_confirmation.same' => 'La confirmation du mot de passe ne correspond pas.',
        ];
    }

    public function withValidator($validator): void
    {
        $this->rejectUnexpectedFields($validator, [
            'name',
            'username',
            'phone',
            'email',
            'password',
            'password_confirmation',
            '_token',
        ]);
    }
}
