<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampagneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'nom'         => $this->nom,
            'annonceur'   => $this->annonceur,
            'description' => $this->description,
            'date_debut'  => $this->date_debut?->format('d/m/Y'),
            'date_fin'    => $this->date_fin?->format('d/m/Y'),
            'statut'      => $this->statut,
            'affiche_path'=> $this->affiche_path,

            'affectations_count' => $this->affectations_count ?? 0,

            'affectations' => $this->whenLoaded(
                'affectations',
                fn () => $this->affectations->map(fn ($a) => [
                    'id'         => $a->id,
                    'face_id'    => $a->face_id,
                    'date_debut' => $a->date_debut?->format('d/m/Y'),
                    'date_fin'   => $a->date_fin?->format('d/m/Y'),
                    'face'       => $a->relationLoaded('face')
                        ? new FaceResource($a->face)
                        : null,
                    'tache'      => $a->relationLoaded('tache') && $a->tache
                        ? ['id' => $a->tache->id, 'statut' => $a->tache->statut]
                        : null,
                ])
            ),

            'created_at' => $this->created_at?->format('d/m/Y'),
        ];
    }
}