<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('gerer utilisateurs') ?? false;
    }

    public function rules(): array
    {
        $userId     = $this->route('user')->id;
        $canManage  = $this->user()->can('gerer utilisateurs');

        return [
            'name'  => ['sometimes', 'required', 'string', 'max:100'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:150',
                Rule::unique('users')->ignore($userId),
            ],

            'password' => ['nullable', 'confirmed', Password::min(8)],

            // Défense en profondeur : même si authorize() bloque déjà,
            // un non-gestionnaire ne peut jamais modifier rôle ou statut.
            'role'  => $canManage
                ? ['sometimes', 'required', 'string', Rule::exists('roles', 'name')]
                : ['prohibited'],
            'actif' => $canManage
                ? ['sometimes', 'boolean']
                : ['prohibited'],
        ];
    }

    public function prepareForValidation(): void
    {
        // Retire password du payload si vide pour ne pas déclencher la validation
        if (empty($this->input('password'))) {
            $this->request->remove('password');
            $this->request->remove('password_confirmation');
        }
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'role.exists'  => 'Le rôle sélectionné est invalide.',
        ];
    }
}