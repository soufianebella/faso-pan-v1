<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Campagne;
use App\Models\Face;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CampagneService
{
    public function __construct(
        protected readonly DisponibiliteService $disponibilite
    ) {}

    public function create(array $data, int $createdBy): Campagne
    {
        return DB::transaction(function () use ($data, $createdBy) {

            // Étape 2 : créer la campagne
            $campagne = Campagne::create([
                ...Arr::except($data, ['face_ids']),
                'created_by' => $createdBy,
                'statut'     => 'preparation',
            ]);

            // Étape 3+4+5 : boucler sur les faces
            foreach ($data['face_ids'] as $faceId) {

                // Créer l'affectation (le pivot contractuel)
                $affectation = $campagne->affectations()->create([
                    'face_id'    => $faceId,
                    'date_debut' => $data['date_debut'],
                    'date_fin'   => $data['date_fin'],
                ]);

                // Mettre à jour le statut de la face → occupee
                Face::where('id', $faceId)
                    ->update(['statut' => 'occupee']);

                // Générer la tâche terrain automatiquement
                $affectation->tache()->create([
                    'statut' => 'en_attente',
                ]);
            }

            // Étape 6 : retourner la campagne avec ses relations
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

            // Libérer toutes les faces de cette campagne
            $faceIds = $campagne->affectations()->pluck('face_id');
            Face::whereIn('id', $faceIds)
                ->update(['statut' => 'libre']);
        });
    }
}