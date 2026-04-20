<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Tache;
use App\Models\User;

class TachePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('taches.manage')
            || $user->can('taches.own.validate');
    }

    public function view(User $user, Tache $tache): bool
    {
        if ($user->can('taches.manage')) {
            return true;
        }

        return $user->can('taches.own.validate')
            && $tache->agent_id === $user->id;
    }

    public function update(User $user, Tache $tache): bool
    {
        if ($user->can('taches.manage')) {
            return true;
        }

        return $user->can('taches.own.validate')
            && $tache->agent_id === $user->id
            && in_array($tache->statut, ['en_attente', 'en_cours'], strict: true);
    }

    public function valider(User $user, Tache $tache): bool
    {
        return $user->can('taches.manage')
            && $tache->statut === 'realisee';
    }

    public function assigner(User $user, Tache $tache): bool
    {
        return $user->can('taches.manage');
    }
}