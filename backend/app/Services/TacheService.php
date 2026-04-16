<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tache;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TacheService
{
    /**
     * Avance le statut d'une tâche selon les règles métier.
     * Agent    : en_attente → en_cours → realisee
     * Gestionnaire : realisee → validee
     */
    public function avancerStatut(Tache $tache, User $user): Tache
    {
        $transitions = [
            'en_attente' => 'en_cours',
            'en_cours'   => 'realisee',
            'realisee'   => 'validee',
        ];

        $nouveauStatut = $transitions[$tache->statut] ?? null;

        if (! $nouveauStatut) {
            throw new \RuntimeException('Cette tache est deja terminee.');
        }

        // Vérification métier : seul gestionnaire peut valider
        if ($nouveauStatut === 'validee' && ! $user->can('taches.manage')) {
            throw new \RuntimeException(
                'Seul un gestionnaire peut valider une tache.'
            );
        }

        DB::transaction(function () use ($tache, $nouveauStatut, $user) {
            $data = ['statut' => $nouveauStatut];

            if ($nouveauStatut === 'realisee') {
                $data['realise_at'] = now();
            }

            if ($nouveauStatut === 'validee') {
                $data['valide_at'] = now();
                $data['valide_by'] = $user->id;
            }

            $tache->update($data);
        });

        return $tache->fresh(['agent', 'validePar', 'affectation.face.panneau']);
    }

    public function assigner(Tache $tache, int $agentId): Tache
    {
        $tache->update(['agent_id' => $agentId]);
        return $tache->fresh('agent');
    }

    /**
     * Retourne la liste filtrée selon le rôle.
     */
    public function lister(User $user, array $filtres = [])
    {
        $query = Tache::with([
            'agent:id,name',
            'affectation.face.panneau:id,reference,ville',
            'affectation.campagne:id,nom,annonceur',
        ]);

        // 1. Super Admin et Gestionnaire voient tout (basé sur la permission manage)
        if ($user->can('taches.manage')) {
            // Pas de restriction supplémentaire
        } 
        // 2. Agent voit seulement ses propres tâches
        elseif ($user->can('taches.own.validate')) {
            $query->where('agent_id', $user->id);
        } 
        // 3. Sécurité : si aucun des deux, on ne retourne rien (ex: annonceur)
        else {
            $query->whereRaw('1 = 0');
        }

        // Filtre par statut si fourni
        if (! empty($filtres['statut'])) {
            $query->where('statut', $filtres['statut']);
        }

        return $query->latest()->paginate(20);
    }
}