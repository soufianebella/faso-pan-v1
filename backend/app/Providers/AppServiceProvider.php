<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Campagne;
use App\Models\Panneau;
use App\Models\Tache;
use App\Models\User;
use App\Policies\CampagnePolicy;
use App\Policies\PanneauPolicy;
use App\Policies\TachePolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(User::class,    UserPolicy::class);
        Gate::policy(Panneau::class, PanneauPolicy::class);
        Gate::policy(Campagne::class, CampagnePolicy::class);
        Gate::policy(Tache::class,   TachePolicy::class);

        // Autoriser tout au super_admin (Bypass des policies)
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });
    }
}