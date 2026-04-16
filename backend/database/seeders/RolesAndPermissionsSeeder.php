<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Permissions 
        $permissions = [
            // Panneaux
            'voir panneaux',
            'creer panneau',
            'modifier panneau',
            'supprimer panneau',

            // Faces
            'voir faces',
            'modifier face',

            // Campagnes
            'assigner campagne',
            'voir campagnes',

            // Tâches
            'taches.manage',       // gestionnaire : tout sur les tâches
            'taches.own.validate', // agent : avancer ses propres tâches

            // Utilisateurs
            'gerer utilisateurs',

            // Dashboard & Exports
            'stats.view',
            'exports.generate',
        ];

        $guards = ['web', 'sanctum'];

        foreach ($permissions as $permission) {
            foreach ($guards as $guard) {
                Permission::firstOrCreate([
                    'name'       => $permission,
                    'guard_name' => $guard,
                ]);
            }
        }

        // ── Rôles 
        foreach ($guards as $guard) {
            $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => $guard]);
            $superAdmin->givePermissionTo(Permission::where('guard_name', $guard)->get());

            $gestionnaire = Role::firstOrCreate(['name' => 'gestionnaire', 'guard_name' => $guard]);
            $gestionnaire->givePermissionTo(Permission::whereIn('name', [
                'voir panneaux',
                'creer panneau',
                'modifier panneau',
                'supprimer panneau',
                'voir faces',
                'modifier face',
                'assigner campagne',
                'voir campagnes',
                'taches.manage',
                'gerer utilisateurs',
                'stats.view',
                'exports.generate',
            ])->where('guard_name', $guard)->get());

            $agent = Role::firstOrCreate(['name' => 'agent_terrain', 'guard_name' => $guard]);
            $agent->givePermissionTo(Permission::whereIn('name', [
                'voir panneaux',
                'voir faces',
                'taches.own.validate',
            ])->where('guard_name', $guard)->get());

            $annonceur = Role::firstOrCreate(['name' => 'annonceur', 'guard_name' => $guard]);
            $annonceur->givePermissionTo(Permission::whereIn('name', [
                'voir campagnes',
            ])->where('guard_name', $guard)->get());
        }

        // ── Utilisateurs de test 
        $users = [
            [
                'name'  => 'Admin Fasopan',
                'email' => 'admin@fasopan.bf',
                'role'  => 'super_admin',
            ],
            [
                'name'  => 'Gestionnaire Fasopan',
                'email' => 'gest@fasopan.bf',
                'role'  => 'gestionnaire',
            ],
            [
                'name'  => 'Agent de Terrain',
                'email' => 'agent@fasopan.bf',
                'role'  => 'agent_terrain',
            ],
            [
                'name'  => 'Annonceur Test',
                'email' => 'annonceur@fasopan.bf',
                'role'  => 'annonceur',
            ],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'              => $data['name'],
                    'password'          => 'password',
                    'actif'             => true,
                    'email_verified_at' => now(),
                ]
            );

            // Sync roles for all guards
            $roles = Role::where('name', $data['role'])->get();
            $user->roles()->sync($roles->pluck('id'));
        }
    }
}