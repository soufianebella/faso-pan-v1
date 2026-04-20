<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tache;
use App\Models\Notification;
use App\Models\Panneau;
use App\Models\Face;
use App\Models\Campagne;
use App\Models\Affectation;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Récupérer les utilisateurs de test existants
        $admin = User::where('email', 'admin@fasopan.bf')->first();
        $gest = User::where('email', 'gest@fasopan.bf')->first();
        $agent = User::where('email', 'agent@fasopan.bf')->first();

        // 2. Créer quelques panneaux et faces
        $panneaux = Panneau::factory()->count(5)->create(['created_by' => $admin->id]);
        foreach ($panneaux as $panneau) {
            Face::factory()->create([
                'panneau_id' => $panneau->id,
                'numero' => 1
            ]);
            Face::factory()->create([
                'panneau_id' => $panneau->id,
                'numero' => 2
            ]);
        }

        // 3. Créer une campagne
        $campagne = Campagne::factory()->create([
            'nom' => 'Campagne Demo FASO PAN',
            'annonceur' => 'Société de Test BF',
            'created_by' => $gest->id
        ]);

        // 4. Créer des affectations et des tâches
        $faces = Face::all();
        
        // Tâche 1 : Assignée à l'agent (Déjà en cours)
        $aff1 = Affectation::factory()->create([
            'campagne_id' => $campagne->id,
            'face_id' => $faces[0]->id
        ]);
        Tache::factory()->create([
            'affectation_id' => $aff1->id,
            'agent_id' => $agent->id,
            'statut' => 'en_cours',
            'note' => 'Installation en cours au quartier Somgandé.'
        ]);

        // Tâche 2 : Non assignée (Pour tester l'assignation par le gestionnaire)
        $aff2 = Affectation::factory()->create([
            'campagne_id' => $campagne->id,
            'face_id' => $faces[1]->id
        ]);
        Tache::factory()->create([
            'affectation_id' => $aff2->id,
            'agent_id' => null,
            'statut' => 'en_attente',
            'note' => 'Besoin d\'un agent pour la pose des affiches.'
        ]);

        // Tâche 3 : Terminée
        $aff3 = Affectation::factory()->create([
            'campagne_id' => $campagne->id,
            'face_id' => $faces[2]->id
        ]);
        Tache::factory()->create([
            'affectation_id' => $aff3->id,
            'agent_id' => $agent->id,
            'statut' => 'realisee',
            'note' => 'Pose effectuée avec succès.'
        ]);

        // 5. Créer des notifications pour l'admin et le gestionnaire
        Notification::factory()->create([
            'user_id' => $admin->id,
            'type' => 'nouvelle_tache',
            'titre' => 'Nouvelle tâche créée',
            'message' => 'Une nouvelle tâche a été créée pour la campagne Demo.',
            'lu_at' => null
        ]);

        Notification::factory()->create([
            'user_id' => $gest->id,
            'type' => 'expiration_j7',
            'titre' => 'Campagne bientôt expirée',
            'message' => 'La campagne Demo se termine dans 7 jours.',
            'lu_at' => null
        ]);

        // Notification lue
        Notification::factory()->create([
            'user_id' => $gest->id,
            'type' => 'campagne_creee',
            'titre' => 'Succès',
            'message' => 'La campagne a été validée.',
            'lu_at' => now()
        ]);
        
        // 6. Notifications pour l'agent
        Notification::factory()->create([
            'user_id' => $agent->id,
            'type' => 'nouvelle_tache',
            'titre' => 'Tâche assignée',
            'message' => 'Vous avez une nouvelle tâche à effectuer.',
            'lu_at' => null
        ]);
    }
}
