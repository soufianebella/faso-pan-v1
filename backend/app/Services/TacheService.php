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
                // valide_by hors $fillable → forceFill() pour contourner le guard
                // Toujours assigné depuis $user->id (jamais depuis la requête HTTP)
                $updates['valide_at'] = now();
                $tache->update($updates);
                $tache->forceFill(['valide_by' => $user->id])->save();
                return;
            }

            $tache->update($updates);
        });

        return $tache->fresh(['agent', 'validePar', 'affectation.face.panneau']);
    }

    /**
     * Met à jour uniquement la photo d'une tâche (sans changer le statut).
     */
    public function updatePhoto(Tache $tache, UploadedFile $photo): Tache
    {
        if ($tache->photo_path) {
            Storage::disk('public')->delete($tache->photo_path);
        }

        $path = $photo->store('taches/' . $tache->id, 'public');

        $tache->update(['photo_path' => $path]);

        return $tache->fresh(['agent', 'affectation.face.panneau']);
    }

    /**
     * Supprime une tâche (soft delete).
     * La Policy garantit que seules les tâches non-validées sont supprimables.
     */
    public function supprimer(Tache $tache): void
    {
        $tache->delete();
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
            'affectation' => fn ($q) => $q->with([
                'face.panneau:id,reference,ville',
                'campagne' => fn ($q) => $q->withTrashed()->select('id', 'nom', 'annonceur'),
            ]),
        ]);
    }

    /**
     * Affectations sans tache — utilisées dans le formulaire de création.
     * Inclut toutes les campagnes (même expirées) : une affectation sans tache
     * représente une pose jamais réalisée — le gestionnaire doit pouvoir régulariser.
     * Exclut les affectations orphelines (campagne ou panneau soft-deleté).
     */
    public function affectationsDisponibles(): \Illuminate\Database\Eloquent\Collection
    {
        return Affectation::with([
            'campagne:id,nom,annonceur,statut',
            'face:id,panneau_id,numero',
            'face.panneau:id,reference,ville',
        ])
        // whereHas applique automatiquement le scope SoftDeletes :
        // seules les campagnes avec deleted_at IS NULL passent le filtre.
        // Les campagnes expirées (statut=expiree, deleted_at=null) sont conservées.
        ->whereHas('campagne')
        // Idem : exclut les affectations dont le panneau a été supprimé.
        ->whereHas('face.panneau')
        ->whereDoesntHave('tache')
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
            // withTrashed : affiche le nom même si la campagne a été supprimée
            'affectation' => fn ($q) => $q->with([
                'face.panneau:id,reference,ville',
                'campagne'   => fn ($q) => $q->withTrashed()->select('id', 'nom', 'annonceur'),
            ]),
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

        if (! empty($filtres['agent_id'])) {
            $query->where('agent_id', $filtres['agent_id']);
        }

        if (! empty($filtres['campagne_id'])) {
            $query->whereHas(
                'affectation',
                fn ($q) => $q->where('campagne_id', $filtres['campagne_id'])
            );
        }

        return $query->latest()->paginate($filtres['per_page'] ?? 20);
    }
}
