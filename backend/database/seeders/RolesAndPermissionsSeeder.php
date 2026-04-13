<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // vider le cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // creer les permissions
        $Permissions = [
            'voir panneaux',
            'creer panneau',
            'modifier panneau',
            'supprimer panneau',
            'voir faces',
            'modifier face',
            'assigner campagne',
            'gerer utilisateurs',
            'voir campagnes'
        ];
        foreach ($Permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // creer les roles et assigner les permissions
        $adminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $adminRole->givePermissionTo(Permission::all());

        $roleGestionnaire = Role::firstOrCreate(['name' => 'gestionnaire']);
        $roleGestionnaire->givePermissionTo([
            'voir panneaux',
            'creer panneau',
            'modifier panneau',
            'voir faces',
            'modifier face',
            'assigner campagne'
        ]);

        $roleAgent = Role::firstOrCreate(['name' => 'agent_terrain']);
        $roleAgent->givePermissionTo(['voir panneaux', 'voir faces', 'modifier face']);

        $annonceur = Role::firstOrCreate(['name' => 'annonceur']);
        $annonceur->givePermissionTo([
            'voir campagnes', // lecture seule sur ses campagnes
        ]);

        // utilisateur de test
        $userData = [
            [
                'name'  => 'Admin Fasopan',
                'email' => 'admin@fasopan.bf',
                'role'  => 'super_admin'
            ],
            [
                'name'  => 'Gestionnaire Fasopan',
                'email' => 'gest@fasopan.bf',
                'role'  => 'gestionnaire'
            ],
            [
                'name'  => 'Agent de Terrain',
                'email' => 'agent@fasopan.bf',
                'role'  => 'agent_terrain'
            ],
        ];

        foreach ($userData as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'              => $data['name'],
                    'password'          => 'password',
                    'actif'         => true,
                    'email_verified_at' => now(),
                ]
            );

            // On synchronise le rôle (évite les doublons si on relance le seeder)
            $user->syncRoles([$data['role']]);
        }
    }
}
