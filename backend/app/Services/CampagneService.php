<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Campagne;
use App\Models\Face;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CampagneService
{
    public function __construct(
        protected readonly DisponibiliteService $disponibilite,
    ) {}

    public function lister(array $filtres = []): LengthAwarePaginator
    {
        return Campagne::with(['affectations.face.panneau'])
            ->withCount('affectations')
            ->when(
                $filtres['search'] ?? null,
                fn ($q, $v) => $q->where(function ($q) use ($v) {
                    $q->where('nom', 'like', "%{$v}%")
                      ->orWhere('annonceur', 'like', "%{$v}%");
                })
            )
            ->when(
                $filtres['statut'] ?? null,
                fn ($q, $v) => $q->where('statut', $v)
            )
            ->when(
                $filtres['annonceur'] ?? null,
                fn ($q, $v) => $q->where('annonceur', 'like', "%{$v}%")
            )
            ->latest()
            ->paginate($filtres['per_page'] ?? 15);
    }

    public function create(array $data, int $createdBy): Campagne
    {
        return DB::transaction(function () use ($data, $createdBy) {

            // Étape 1 : créer la campagne
            $campagne = Campagne::create([
                ...Arr::except($data, ['face_ids']),
                'created_by' => $createdBy,
                'statut'     => 'preparation',
            ]);

            // Étape 2 : boucler sur les faces
            foreach ($data['face_ids'] as $faceId) {

                // Créer l'affectation
                $affectation = $campagne->affectations()->create([
                    'face_id'    => $faceId,
                    'date_debut' => $data['date_debut'],
                    'date_fin'   => $data['date_fin'],
                ]);

                // Marquer la face comme occupée
                Face::where('id', $faceId)
                    ->update(['statut' => 'occupee']);

                // Pas de tâche automatique : le gestionnaire crée les tâches
                // manuellement depuis le module Tâches, avec l'agent de son choix.
            }

            // Étape 3 : retourner la campagne complète
            // Les tâches seront créées manuellement par le gestionnaire (statut null ici).
            return $campagne->load([
                'affectations.face.panneau',
                'affectations.tache',
            ]);
        });
    }

    public function update(Campagne $campagne, array $data): Campagne
    {
        return DB::transaction(function () use ($campagne, $data) {
            $campagne->update(Arr::except($data, ['face_ids']));
            return $campagne->fresh();
        });
    }

    public function cloturer(Campagne $campagne): void
    {
        DB::transaction(function () use ($campagne) {
            $campagne->update(['statut' => 'expiree']);

            $faceIds = $campagne->affectations()->pluck('face_id');
            Face::whereIn('id', $faceIds)
                ->update(['statut' => 'libre']);
        });
    }

    /**
     * Suppression d'une campagne expiree.
     * Soft delete uniquement la campagne : deleted_at est rempli,
     * la ligne disparait des listes (scope SoftDeletes par defaut).
     * Les affectations et taches associees restent en base pour l'audit
     * et les statistiques historiques. Les faces sont deja 'libre' depuis
     * la cloture. Une restauration reste possible via restore() si besoin.
     */
    public function supprimer(Campagne $campagne): void
    {
        $campagne->delete();
    }
}