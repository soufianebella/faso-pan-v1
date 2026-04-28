<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'numero'  => $this->numero,
            'largeur' => $this->largeur,
            'hauteur' => $this->hauteur,
            'surface' => $this->surface,
            'statut'  => $this->statut,

            // Relation panneau (optionnelle — chargée depuis TacheResource)
            'panneau' => $this->whenLoaded('panneau', fn () => [
                'id'        => $this->panneau->id,
                'reference' => $this->panneau->reference,
                'ville'     => $this->panneau->ville,
                'quartier'  => $this->panneau->quartier,
            ]),

            // Affectation active pour la page de détail panneau
            'affectation_active' => $this->whenLoaded(
                'affectationActive',
                fn () => $this->affectationActive
                    ? $this->buildAffectationActive($this->affectationActive)
                    : null,
            ),
        ];
    }

    private function buildAffectationActive(object $aff): array
    {
        $dateDebut = $aff->date_debut instanceof \Carbon\Carbon
            ? $aff->date_debut
            : Carbon::parse($aff->date_debut);

        $dateFin = $aff->date_fin instanceof \Carbon\Carbon
            ? $aff->date_fin
            : Carbon::parse($aff->date_fin);

        $joursRestants = (int) Carbon::now()->diffInDays($dateFin, false);

        return [
            'campagne_nom'       => $aff->campagne?->nom,
            'campagne_annonceur' => $aff->campagne?->annonceur,
            'date_debut'         => $dateDebut->format('d/m/Y'),
            'date_fin'           => $dateFin->format('d/m/Y'),
            'jours_restants'     => $joursRestants,
        ];
    }
}
