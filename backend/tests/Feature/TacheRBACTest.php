<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tache;
use App\Models\Affectation;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TacheRBACTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /**
     * TEST 1 : Authentification admin - GET /api/v1/taches avec token admin 
     * doit retourner 200 + toutes les tâches avec leurs relations complètes
     */
    public function test_admin_can_list_all_taches_with_relations()
    {
        $admin = User::where('email', 'admin@fasopan.bf')->first();
        
        // Créer des tâches pour différents agents
        Tache::factory()->count(5)->create();

        $response = $this->actingAs($admin, 'sanctum')
                         ->getJson('/api/v1/taches');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    }

    /**
     * NOUVEAU TEST : Authentification gestionnaire - GET /api/v1/taches
     * doit retourner 200 + toutes les tâches (comme l'admin)
     */
    public function test_gestionnaire_can_list_all_taches()
    {
        $gestionnaire = User::where('email', 'gest@fasopan.bf')->first();
        
        // Créer des tâches pour différents agents
        Tache::factory()->count(5)->create();

        $response = $this->actingAs($gestionnaire, 'sanctum')
                         ->getJson('/api/v1/taches');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    }

    /**
     * TEST 2 : Authentification agent - GET /api/v1/taches avec token agent 
     * doit retourner 200 + uniquement les tâches assignées à cet agent
     */
    public function test_agent_can_list_only_assigned_taches()
    {
        $agent = User::where('email', 'agent@fasopan.bf')->first();
        
        // 3 tâches pour lui
        Tache::factory()->count(3)->create(['agent_id' => $agent->id]);
        
        // 2 tâches pour quelqu'un d'autre
        Tache::factory()->count(2)->create();

        $response = $this->actingAs($agent, 'sanctum')
                         ->getJson('/api/v1/taches');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    /**
     * NOUVEAU TEST : Authentification annonceur - GET /api/v1/taches
     * doit retourner 403 Forbidden
     */
    public function test_annonceur_cannot_access_taches()
    {
        $annonceur = User::where('email', 'annonceur@fasopan.bf')->first();
        
        $response = $this->actingAs($annonceur, 'sanctum')
                         ->getJson('/api/v1/taches');

        $response->assertStatus(403);
    }

    /**
     * TEST 3 : Changement de statut - PATCH /api/v1/taches/1/avancer 
     * avec token agent sur sa propre tâche doit retourner 200 + statut mis à jour à "en_cours"
     */
    public function test_agent_can_advance_own_tache_status()
    {
        $agent = User::where('email', 'agent@fasopan.bf')->first();
        $tache = Tache::factory()->create([
            'agent_id' => $agent->id,
            'statut' => 'en_attente'
        ]);

        $response = $this->actingAs($agent, 'sanctum')
                         ->patchJson("/api/v1/taches/{$tache->id}/avancer");

        $response->assertStatus(200);
        $response->assertJsonPath('data.statut', 'en_cours');
        $this->assertEquals('en_cours', $tache->fresh()->statut);
    }

    /**
     * TEST 4 : Assignation d'agent - PATCH /api/v1/taches/1/assigner 
     * avec token gestionnaire et body { "agent_id": 3 } doit retourner 200 + tâche avec agent assigné
     */
    public function test_gestionnaire_can_assign_agent_to_tache()
    {
        $gestionnaire = User::where('email', 'gest@fasopan.bf')->first();
        $agent = User::where('email', 'agent@fasopan.bf')->first();
        $tache = Tache::factory()->create(['agent_id' => null]);

        $response = $this->actingAs($gestionnaire, 'sanctum')
                         ->patchJson("/api/v1/taches/{$tache->id}/assigner", [
                             'agent_id' => $agent->id
                         ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.agent.id', $agent->id);
        $this->assertEquals($agent->id, $tache->fresh()->agent_id);
    }
}
