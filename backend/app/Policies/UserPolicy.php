<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $authUser): bool
    {
        return $authUser->can('gerer utilisateurs');
    }

    public function view(User $authUser, User $targetUser): bool
    {
        // Admin/Gestionnaire voient tout le monde
        // Un utilisateur peut toujours consulter son propre profil
        return $authUser->can('gerer utilisateurs')
            || $authUser->id === $targetUser->id;
    }

    public function create(User $authUser): bool
    {
        return $authUser->can('gerer utilisateurs');
    }

    public function update(User $authUser, User $targetUser): bool
    {
        // Peut modifier si permission globale OU modification de son propre profil
        return $authUser->can('gerer utilisateurs')
            || $authUser->id === $targetUser->id;
    }

    public function delete(User $authUser, User $targetUser): bool
    {
        // La vérification anti-auto-désactivation est dans le Service
        // La Policy vérifie uniquement la permission
        return $authUser->can('gerer utilisateurs')
            && $authUser->id !== $targetUser->id;
    }
}