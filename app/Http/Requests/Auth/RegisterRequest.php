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

        if (is_string($this->input('email'))) {
            $normalized['email'] = Str::lower(trim($this->input('email')));
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
            'name' => ['bail', 'required', 'string', 'max:255'],
            'email' => ['bail', 'required', 'string', 'email:rfc', 'max:255', 'unique:'.User::class],
            'password' => ['bail', 'required', 'string', Password::defaults(), new PasswordWithinHashLimit],
            'password_confirmation' => ['bail', 'required', 'string', 'same:password'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire.',
            'name.string' => 'Le nom doit être une chaîne de caractères.',
            'name.max' => 'Le nom ne doit pas dépasser :max caractères.',
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.string' => 'L\'adresse e-mail doit être une chaîne de caractères.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
            'email.max' => 'L\'adresse e-mail ne doit pas dépasser :max caractères.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.string' => 'Le mot de passe doit être une chaîne de caractères.',
            'password.min' => 'Le mot de passe doit contenir au moins :min caractères.',
            'password_confirmation.required' => 'La confirmation du mot de passe est obligatoire.',
            'password_confirmation.string' => 'La confirmation du mot de passe doit être une chaîne de caractères.',
            'password_confirmation.same' => 'La confirmation du mot de passe ne correspond pas.',
        ];
    }

    public function withValidator($validator): void
    {
        $this->rejectUnexpectedFields($validator, [
            'name',
            'email',
            'password',
            'password_confirmation',
            '_token',
        ]);
    }
}
