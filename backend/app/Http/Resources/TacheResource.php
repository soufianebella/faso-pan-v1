<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TacheResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'statut'         => $this->statut,
            'note'           => $this->note,
            'realise_at'     => $this->realise_at?->format('d/m/Y H:i'),
            'valide_at'      => $this->valide_at?->format('d/m/Y H:i'),

            'agent'          => $this->whenLoaded('agent', fn () => [
                'id'   => $this->agent->id,
                'name' => $this->agent->name,
            ]),

            'valide_par'     => $this->whenLoaded('validePar', fn () => [
                'id'   => $this->validePar->id,
                'name' => $this->validePar->name,
            ]),

            'affectation'    => $this->whenLoaded('affectation', fn () => [
                'id'         => $this->affectation->id,
                'date_debut' => $this->affectation->date_debut?->format('d/m/Y'),
                'date_fin'   => $this->affectation->date_fin?->format('d/m/Y'),
                'face'       => $this->affectation->relationLoaded('face')
                    ? new FaceResource($this->affectation->face)
                    : null,
                'campagne'   => $this->affectation->relationLoaded('campagne')
                    ? [
                        'id'       => $this->affectation->campagne->id,
                        'nom'      => $this->affectation->campagne->nom,
                        'annonceur'=> $this->affectation->campagne->annonceur,
                    ]
                    : null,
            ]),
        ];
    }
}