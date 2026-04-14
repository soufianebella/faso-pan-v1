<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Panneau;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PanneauService
{
    public function create(array $data, int $createdBy): Panneau
    {
        return DB::transaction(function () use ($data, $createdBy) {

            // Étape 1 : créer le panneau sans les faces
            $panneau = Panneau::create([
                ...Arr::except($data, ['faces']),
                'created_by' => $createdBy,
            ]);

            // Étape 2 : créer toutes les faces en une requête
            // createMany() génère un INSERT par face
            // surface est calculée automatiquement par MySQL (storedAs)
            $panneau->faces()->createMany($data['faces']);

            // Étape 3 : recharger avec les faces pour la réponse
            return $panneau->load('faces');
        });
    }

    public function update(Panneau $panneau, array $data): Panneau
    {
        return DB::transaction(function () use ($panneau, $data) {

            // Update uniquement les infos du panneau
            // Les faces ne sont pas modifiables (contrats en cours)
            $panneau->update(Arr::except($data, ['faces']));

            return $panneau->fresh('faces');
        });
    }

    public function archive(Panneau $panneau): void
    {
        DB::transaction(function () use ($panneau) {

            // Vérifie qu'aucune face n'a d'affectation active
            $facesOccupees = $panneau->faces()
                ->where('statut', 'occupee')
                ->exists();

            if ($facesOccupees) {
                throw new \RuntimeException(
                    'Impossible d\'archiver : ce panneau a des faces occupées.'
                );
            }

            // Soft delete du panneau — les faces restent en base
            $panneau->delete();
        });
    }

    /**
     * Filtre la liste des panneaux selon les paramètres
     * reçus depuis le Controller.
     * Logique centralisée ici — pas dans le Controller.
     */
    public function filtrer(array $filtres)
    {
        $query = Panneau::with(['faces'])
            ->withCount([
                'faces',
                'faces as faces_libres_count' => fn($q) =>
                $q->where('statut', 'libre'),
            ]);

        if (!empty($filtres['ville'])) {
            $query->where('ville', $filtres['ville']);
        }

        if (!empty($filtres['statut'])) {
            $query->where('statut', $filtres['statut']);
        }

        if (isset($filtres['eclaire'])) {
            $query->where('eclaire', (bool) $filtres['eclaire']);
        }

        if (!empty($filtres['search'])) {
            $query->where(function ($q) use ($filtres) {
                $q->where('reference', 'like', "%{$filtres['search']}%")
                    ->orWhere('ville', 'like', "%{$filtres['search']}%")
                    ->orWhere('quartier', 'like', "%{$filtres['search']}%");
            });
        }

        return $query->latest()->paginate(15);
    }
}
