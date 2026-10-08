<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateAdminUserRequest extends FormRequest
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
        /** @var User $user */
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'role' => ['required', Rule::in(UserRole::values())],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire.',
            'name.min' => 'Le nom doit contenir au moins :min caractères.',
            'email.required' => 'L\'e-mail est obligatoire.',
            'email.email' => 'L\'e-mail n\'est pas valide.',
            'email.unique' => 'Cet e-mail est déjà utilisé.',
            'role.required' => 'Le rôle est obligatoire.',
            'role.in' => 'Le rôle sélectionné n\'est pas valide.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var User $user */
            $user = $this->route('user');
            $role = $this->input('role');

            if ($role === UserRole::Provider->value) {
                $profile = $user->serviceProvider;
                if ($profile === null || $profile->status !== 'approved') {
                    $validator->errors()->add(
                        'role',
                        'Le rôle prestataire nécessite un profil prestataire approuvé (menu Prestataires).'
                    );
                }
            }
        });
    }
}
