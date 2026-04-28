<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Affectation;
use App\Models\Panneau;
use App\Models\PanneauEtat;
use App\Models\Tache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
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
            // surface est calculée automatiquement par MySQL (storedAs)
            $panneau->faces()->createMany($data['faces']);

            // Étape 3 : recharger avec les faces pour la réponse
            return $panneau->load('faces');
        });
    }

    public function update(Panneau $panneau, array $data): Panneau
    {
        return DB::transaction(function () use ($panneau, $data) {

            // Les faces ne sont pas modifiables (contrats en cours)
            $panneau->update(Arr::except($data, ['faces']));

            return $panneau->fresh('faces');
        });
    }

    public function archive(Panneau $panneau): void
    {
        DB::transaction(function () use ($panneau) {

            $facesOccupees = $panneau->faces()
                ->where('statut', 'occupee')
                ->exists();

            if ($facesOccupees) {
                throw new \RuntimeException(
                    'Impossible d\'archiver : ce panneau a des faces occupées.'
                );
            }

            $panneau->delete();
        });
    }

    /**
     * Change le statut d'un panneau avec traçabilité complète.
     * Crée une entrée dans panneau_etats pour l'audit.
     */
    public function changerStatut(
        Panneau $panneau,
        string  $statut,
        string  $motif,
        int     $changedBy
    ): Panneau {
        return DB::transaction(function () use ($panneau, $statut, $motif, $changedBy) {

            // Récupère la valeur brute de l'enum avant la mise à jour
            $statutAvant = $panneau->getRawOriginal('statut');

            $panneau->update(['statut' => $statut]);

            PanneauEtat::create([
                'panneau_id'   => $panneau->id,
                'statut_avant' => $statutAvant,
                'statut_apres' => $statut,
                'motif'        => $motif,
                'changed_by'   => $changedBy,
            ]);

            return $panneau->fresh('faces');
        });
    }

    /**
     * Timeline unifiée des événements d'un panneau.
     * Agrège : changements de statut + campagnes + tâches terrain.
     * Triée par date DESC, limitée à 20 entrées.
     */
    public function historique(Panneau $panneau): array
    {
        $events = collect();

        // ── 1. Changements de statut ─────────────────────────────────────────
        $etats = $panneau->etats()
            ->with('changedBy')
            ->latest()
            ->limit(20)
            ->get();

        foreach ($etats as $etat) {
            $events->push([
                'type'     => 'statut',
                'titre'    => "Statut changé : {$etat->statut_avant} → {$etat->statut_apres}",
                'detail'   => $etat->motif,
                'date'     => $etat->created_at->diffForHumans(),
                'date_iso' => $etat->created_at->toIso8601String(),
                'icone'    => 'fa-circle-half-stroke',
                'couleur'  => '#1B3B8A',
                'auteur'   => $etat->changedBy?->name,
            ]);
        }

        // ── 2. Campagnes affectées ────────────────────────────────────────────
        $faceIds = $panneau->faces()->pluck('id');

        if ($faceIds->isNotEmpty()) {
            $affectations = Affectation::whereIn('face_id', $faceIds)
                ->with(['campagne', 'face'])
                ->latest()
                ->limit(20)
                ->get();

            foreach ($affectations as $aff) {
                $dateDebut = Carbon::parse($aff->date_debut);
                $dateFin   = Carbon::parse($aff->date_fin);

                $events->push([
                    'type'     => 'affectation',
                    'titre'    => sprintf(
                        '%s — Face %s',
                        $aff->campagne?->annonceur ?? $aff->campagne?->nom ?? '—',
                        $aff->face?->numero ?? '?'
                    ),
                    'detail'   => sprintf(
                        '%s → %s',
                        $dateDebut->format('d/m/Y'),
                        $dateFin->format('d/m/Y')
                    ),
                    'date'     => $aff->created_at->diffForHumans(),
                    'date_iso' => $aff->created_at->toIso8601String(),
                    'icone'    => 'fa-bullhorn',
                    'couleur'  => '#F97316',
                    'auteur'   => null,
                ]);
            }

            // ── 3. Tâches terrain ────────────────────────────────────────────
            $affectationIds = $affectations->pluck('id');

            if ($affectationIds->isNotEmpty()) {
                $taches = Tache::whereIn('affectation_id', $affectationIds)
                    ->with(['agent', 'affectation.campagne', 'affectation.face'])
                    ->latest()
                    ->limit(20)
                    ->get();

                foreach ($taches as $tache) {
                    $label = match ($tache->statut) {
                        'validee'  => 'Tâche validée',
                        'realisee' => 'Tâche réalisée',
                        'en_cours' => 'Tâche en cours',
                        default    => 'Tâche créée',
                    };

                    $agentName = $tache->agent?->name ?? 'Non assigné';

                    $events->push([
                        'type'           => 'tache',
                        'id'             => $tache->id,
                        'titre'          => "{$label} — {$agentName}",
                        'detail'         => $tache->note,
                        'date'           => $tache->created_at->diffForHumans(),
                        'date_iso'       => $tache->created_at->toIso8601String(),
                        'icone'          => 'fa-list-check',
                        'couleur'        => '#27AE60',
                        'auteur'         => $tache->agent?->name,
                        'statut_tache'   => $tache->statut,
                        'campagne_nom'   => $tache->affectation?->campagne?->nom,
                        'face_numero'    => $tache->affectation?->face?->numero,
                        'realise_at_fmt' => $tache->realise_at?->format('d/m/Y'),
                    ]);
                }
            }
        }

        return $events
            ->sortByDesc('date_iso')
            ->take(20)
            ->values()
            ->toArray();
    }

    /**
     * Photos de terrain liées à un panneau (via affectations → tâches avec photo).
     * Retourne une liste plate triée par date de réalisation DESC.
     */
    public function photos(Panneau $panneau): array
    {
        $faceIds = $panneau->faces()->pluck('id');

        if ($faceIds->isEmpty()) {
            return [];
        }

        $affectationIds = Affectation::whereIn('face_id', $faceIds)->pluck('id');

        if ($affectationIds->isEmpty()) {
            return [];
        }

        return Tache::whereIn('affectation_id', $affectationIds)
            ->whereNotNull('photo_path')
            ->with(['affectation.face', 'affectation.campagne', 'agent'])
            ->latest('realise_at')
            ->get()
            ->map(fn (Tache $tache) => [
                'photo_url'          => Storage::url($tache->photo_path),
                'face_numero'        => $tache->affectation?->face?->numero,
                'campagne_nom'       => $tache->affectation?->campagne?->nom,
                'agent_name'         => $tache->agent?->name,
                'realise_at'         => $tache->realise_at?->format('d/m/Y'),
            ])
            ->values()
            ->toArray();
    }

    /**
     * Filtre la liste des panneaux selon les paramètres reçus depuis le Controller.
     */
    public function filtrer(array $filtres)
    {
        $query = Panneau::with(['faces'])
            ->withCount([
                'faces',
                'faces as faces_libres_count' => fn ($q) =>
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
                  ->orWhere('ville',    'like', "%{$filtres['search']}%")
                  ->orWhere('quartier', 'like', "%{$filtres['search']}%");
            });
        }

        return $query->latest()->paginate(15);
    }
}
