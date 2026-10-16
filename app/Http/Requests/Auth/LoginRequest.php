<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Concerns\RejectsUnexpectedFields;
use App\Rules\PasswordWithinHashLimit;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    use RejectsUnexpectedFields;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! is_string($this->input('login'))) {
            return;
        }

        $this->merge([
            'login' => Str::lower(trim($this->input('login'))),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => [
                'bail',
                'required',
                'string',
                'max:255',
            ],
            'password' => [
                'bail',
                'required',
                'string',
                new PasswordWithinHashLimit,
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'login.required' => 'Le nom d\'utilisateur ou l\'email est obligatoire.',
            'login.string' => 'L\'identifiant doit être une chaîne de caractères.',
            'login.max' => 'L\'identifiant ne doit pas dépasser :max caractères.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.string' => 'Le mot de passe doit être une chaîne de caractères.',
        ];
    }

    public function withValidator($validator): void
    {
        $this->rejectUnexpectedFields($validator, [
            'login',
            'password',
            'remember',
            '_token',
        ]);

        $validator->after(function ($validator) {
            if (
                $this->exists('remember') &&
                ! is_scalar($this->input('remember'))
            ) {
                $validator->errors()->add(
                    'remember',
                    'La valeur « se souvenir de moi » n\'est pas valide.'
                );
            }
        });
    }

    /**
     * Attempt to authenticate using username or email.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = $this->input('login');

        $field = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'username';

        $credentials = [
            $field => $login,
            'password' => $this->input('password'),
        ];

        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => 'Ces identifiants ne correspondent pas.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => 'Trop de tentatives. Réessayez dans '.$seconds.' secondes.',
        ]);
    }

    /**
     * Generate the login throttle key.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(
            Str::lower($this->string('login')).'|'.$this->ip()
        );
    }
}
