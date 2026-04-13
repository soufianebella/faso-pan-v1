<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Panneau;
use App\Models\User;

class PanneauPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('voir panneaux');
    }

    public function view(User $user, Panneau $panneau): bool
    {
        return $user->can('voir panneaux');
    }

    public function create(User $user): bool
    {
        return $user->can('creer panneau');
    }

    public function update(User $user, Panneau $panneau): bool
    {
        return $user->can('modifier panneau');
    }

    public function delete(User $user, Panneau $panneau): bool
    {
        return $user->can('supprimer panneau');
    }
}