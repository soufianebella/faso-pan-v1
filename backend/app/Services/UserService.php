<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function create(array $data, string $role): User
    {
        return DB::transaction(function () use ($data, $role) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => $data['password'],
                'actif'    => true,
            ]);

            $user->assignRole($role);

            return $user;
        });
    }

    public function update(User $user, array $data, ?string $role): User
    {
        return DB::transaction(function () use ($user, $data, $role) {
            // Retire 'role' du tableau avant de passer à Eloquent
            // 'role' n'est pas une colonne de la table users
            $userData = Arr::except($data, ['role', 'password_confirmation']);

            // Le cast 'hashed' du modèle hash automatiquement le password
            // S'il est absent du tableau, Eloquent ne touche pas au champ existant
            if (empty($userData['password'])) {
                unset($userData['password']);
            }

            $user->update($userData);

            // syncRoles() remplace — on ne cumule jamais les rôles dans ce système
            if ($role) {
                $user->syncRoles([$role]);
            }

            return $user->fresh(); // recharge depuis la DB avec les nouvelles données
        });
    }

    public function updateProfile(User $user, array $data): User
    {
        $userData = Arr::except($data, ['password_confirmation']);

        if (empty($userData['password'])) {
            unset($userData['password']);
        }

        $user->update($userData);

        return $user->fresh();
    }

    public function deactivate(User $user, User $admin): void
    {
        // Validation métier : hors transaction car aucune opération DB impliquée
        if ($user->id === $admin->id) {
            throw new \InvalidArgumentException(
                'Un administrateur ne peut pas désactiver son propre compte.'
            );
        }

        DB::transaction(function () use ($user) {
            $user->update(['actif' => false]);

            // Révocation immédiate de tous les tokens
            // Force la déconnexion sur tous les appareils
            $user->tokens()->delete();
        });
    }
}