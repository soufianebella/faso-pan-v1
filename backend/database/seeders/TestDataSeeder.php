<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Affectation;
use App\Models\Campagne;
use App\Models\Face;
use App\Models\Panneau;
use App\Models\Tache;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::where('email', 'admin@fasopan.bf')->first();
        $agent  = User::where('email', 'agent@fasopan.bf')->first();

        // ── Panneaux 
        $panneau1 = Panneau::create([
            'reference'  => 'PAN-2026-001',
            'ville'      => 'Ouagadougou',
            'quartier'   => 'Patte d\'Oie',
            'latitude'   => 12.3647,
            'longitude'  => -1.5337,
            'eclaire'    => true,
            'hauteur_mat'=> 8,
            'statut'     => 'actif',
            'created_by' => $admin->id,
        ]);

        $panneau2 = Panneau::create([
            'reference'  => 'PAN-2026-002',
            'ville'      => 'Bobo-Dioulasso',
            'quartier'   => 'Secteur 22',
            'eclaire'    => false,
            'hauteur_mat'=> 6,
            'statut'     => 'actif',
            'created_by' => $admin->id,
        ]);

        // ── Faces 
        $face1 = Face::create([
            'panneau_id' => $panneau1->id,
            'numero'     => 1,
            'largeur'    => 4,
            'hauteur'    => 3,
            'statut'     => 'occupee',
        ]);

        $face2 = Face::create([
            'panneau_id' => $panneau1->id,
            'numero'     => 2,
            'largeur'    => 4,
            'hauteur'    => 3,
            'statut'     => 'libre',
        ]);

        $face3 = Face::create([
            'panneau_id' => $panneau2->id,
            'numero'     => 1,
            'largeur'    => 3,
            'hauteur'    => 2,
            'statut'     => 'libre',
        ]);

        // ── Campagne 
        $campagne = Campagne::create([
            'nom'        => 'Campagne BRAKINA Mai 2026',
            'annonceur'  => 'BRAKINA BEER',
            'description'=> 'Campagne nationale brasserie',
            'date_debut' => '2026-05-01',
            'date_fin'   => '2026-05-31',
            'statut'     => 'preparation',
            'created_by' => $admin->id,
        ]);

        // ── Affectation + Tâche 
        $affectation = Affectation::create([
            'campagne_id' => $campagne->id,
            'face_id'     => $face1->id,
            'date_debut'  => '2026-05-01',
            'date_fin'    => '2026-05-31',
        ]);

        Tache::create([
            'affectation_id' => $affectation->id,
            'agent_id'       => $agent->id,
            'statut'         => 'en_attente',
        ]);

        // ── Deuxième campagne avec tâche en_cours 
        $campagne2 = Campagne::create([
            'nom'        => 'Campagne SOTRACO Juin 2026',
            'annonceur'  => 'SOTRACO',
            'date_debut' => '2026-06-01',
            'date_fin'   => '2026-06-30',
            'statut'     => 'preparation',
            'created_by' => $admin->id,
        ]);

        $affectation2 = Affectation::create([
            'campagne_id' => $campagne2->id,
            'face_id'     => $face3->id,
            'date_debut'  => '2026-06-01',
            'date_fin'    => '2026-06-30',
        ]);

        Tache::create([
            'affectation_id' => $affectation2->id,
            'agent_id'       => null,
            'statut'         => 'en_attente',
        ]);

        $this->command->info('Donnees de test creees avec succes.');
    }
}