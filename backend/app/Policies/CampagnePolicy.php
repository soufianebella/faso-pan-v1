<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Campagne;
use App\Models\User;

class CampagnePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('assigner campagne')
            || $user->can('voir campagnes');
    }

    public function view(User $user, Campagne $campagne): bool
    {
        return $user->can('assigner campagne')
            || $user->can('voir campagnes');
    }

    public function create(User $user): bool
    {
        return $user->can('assigner campagne');
    }

    public function delete(User $user, Campagne $campagne): bool
    {
        return $user->can('assigner campagne');
    }
}