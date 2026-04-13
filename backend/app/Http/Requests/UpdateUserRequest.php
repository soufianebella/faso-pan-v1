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
        // $this->route('user') retourne le modèle User injecté par Route Model Binding
        $userId = $this->route('user')->id;

        return [
            'name'  => ['sometimes', 'required', 'string', 'max:100'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:150',
                // Ignore l'utilisateur courant pour éviter le faux conflit unique
                Rule::unique('users')->ignore($userId),
            ],

            // nullable : si absent ou null, on ne touche pas au mot de passe
            'password' => ['nullable', 'confirmed', Password::min(8)],

            'role'  => ['sometimes', 'required', 'string', Rule::exists('roles', 'name')],
            'actif' => ['sometimes', 'boolean'],
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