<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Affectation;
use App\Models\Face;
use Illuminate\Support\Collection;

final class DisponibiliteService
{
    /**
     * Vérifie si UNE face est disponible sur une période.
     * Utilisé pour la modification d'une affectation existante.
     */
    public function estDisponible(
        int    $faceId,
        string $debut,
        string $fin,
        ?int   $excludeAffectationId = null
    ): bool {
        return ! Affectation::where('face_id', $faceId)
            ->chevauche($debut, $fin)
            ->when(
                $excludeAffectationId,
                fn ($q) => $q->where('id', '!=', $excludeAffectationId)
            )
            ->exists();
    }

    /**
     * Retourne toutes les faces libres sur une période.
     * Utilisé pour afficher les faces disponibles dans le formulaire.
     * Une seule requête SQL avec sous-requête NOT IN.
     */
    public function facesDisponibles(
        string $debut,
        string $fin
    ): Collection {
        $facesOccupees = Affectation::chevauche($debut, $fin)
            ->select('face_id');

        // Pas de filtre sur face.statut : une face 'occupee' peut être libre
        // sur une période future. Le whereNotIn(chevauche) est la seule source de vérité.
        return Face::whereNotIn('id', $facesOccupees)
            ->select([
                'id', 'panneau_id', 'numero',
                'largeur', 'hauteur', 'surface',
            ])
            ->with(['panneau:id,reference,ville,quartier,eclaire'])
            ->get();
    }

    /**
     * Retourne les IDs des faces en conflit sur une période.
     * UNE seule requête SQL pour toutes les faces.
     * Utilisé dans StoreCampagneRequest::withValidator().
     *
     * @param  array<int>  $faceIds
     * @return array<int>
     */
    public function conflits(
        array  $faceIds,
        string $debut,
        string $fin,
        ?int   $excludeAffectationId = null
    ): array {
        return Affectation::whereIn('face_id', $faceIds)
            ->chevauche($debut, $fin)
            ->when(
                $excludeAffectationId,
                fn ($q) => $q->where('id', '!=', $excludeAffectationId)
            )
            ->distinct()
            ->pluck('face_id')
            ->toArray();
    }
}