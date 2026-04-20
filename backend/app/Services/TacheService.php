<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Affectation;
use App\Models\Tache;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TacheService
{
    /**
     * Avance le statut d'une tâche selon les règles métier.
     * Agent        : en_attente → en_cours → realisee (photo obligatoire)
     * Gestionnaire : realisee    → validee
     *
     * @param array{
     *     photo?: UploadedFile|null,
     *     note?: string|null,
     *     latitude_pose?: float|null,
     *     longitude_pose?: float|null,
     * } $data
     */
    public function avancerStatut(Tache $tache, User $user, array $data = []): Tache
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

        if ($nouveauStatut === 'validee' && ! $user->can('taches.manage')) {
            throw new \RuntimeException(
                'Seul un gestionnaire peut valider une tache.'
            );
        }

        DB::transaction(function () use ($tache, $nouveauStatut, $user, $data) {
            $updates = ['statut' => $nouveauStatut];

            if ($nouveauStatut === 'realisee') {
                $updates['realise_at'] = now();

                // Photo de preuve — obligatoire via FormRequest
                if (! empty($data['photo']) && $data['photo'] instanceof UploadedFile) {
                    // Supprime l'ancienne photo si elle existe (ré-upload)
                    if ($tache->photo_path) {
                        Storage::disk('public')->delete($tache->photo_path);
                    }

                    $updates['photo_path'] = $data['photo']->store(
                        'taches/' . $tache->id,
                        'public'
                    );
                }

                if (! empty($data['note'])) {
                    $updates['note'] = $data['note'];
                }

                if (isset($data['latitude_pose'])) {
                    $updates['latitude_pose'] = $data['latitude_pose'];
                }

                if (isset($data['longitude_pose'])) {
                    $updates['longitude_pose'] = $data['longitude_pose'];
                }
            }

            if ($nouveauStatut === 'validee') {
                $updates['valide_at'] = now();
                $updates['valide_by'] = $user->id;
            }

            $tache->update($updates);
        });

        return $tache->fresh(['agent', 'validePar', 'affectation.face.panneau']);
    }

    public function assigner(Tache $tache, int $agentId): Tache
    {
        $tache->update(['agent_id' => $agentId]);
        return $tache->fresh('agent');
    }

    /**
     * Crée une tâche pour une affectation.
     * Statut initial : en_attente.
     */
    public function creer(array $data): Tache
    {
        $tache = DB::transaction(function () use ($data): Tache {
            return Tache::create([
                'affectation_id' => $data['affectation_id'],
                'agent_id'       => $data['agent_id'] ?? null,
                'statut'         => 'en_attente',
                'note'           => $data['note'] ?? null,
            ]);
        });

        return $tache->load([
            'agent:id,name',
            'affectation.face.panneau:id,reference,ville',
            'affectation.campagne:id,nom,annonceur',
        ]);
    }

    /**
     * Affectations sans tache — utilisées dans le formulaire de création.
     */
    public function affectationsDisponibles(): \Illuminate\Database\Eloquent\Collection
    {
        return Affectation::with([
            'campagne:id,nom,annonceur,statut',
            'face:id,panneau_id,numero',
            'face.panneau:id,reference,ville',
        ])
        ->whereDoesntHave('tache')
        ->whereHas('campagne', fn ($q) => $q->whereIn('statut', ['preparation', 'active']))
        ->orderBy('date_fin')
        ->get();
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

        if ($user->can('taches.manage')) {
            // Pas de restriction supplémentaire
        } elseif ($user->can('taches.own.validate')) {
            $query->pourAgent($user->id);
        } else {
            $query->whereRaw('1 = 0');
        }

        if (! empty($filtres['statut'])) {
            $query->where('statut', $filtres['statut']);
        }

        return $query->latest()->paginate(20);
    }
}
