<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PanneauResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'reference'   => $this->reference,
            'pays'        => $this->pays,
            'ville'       => $this->ville,
            'quartier'    => $this->quartier,
            'adresse'     => $this->adresse,
            'latitude'    => $this->latitude,
            'longitude'   => $this->longitude,
            'eclaire'     => $this->eclaire,
            'hauteur_mat' => $this->hauteur_mat,
            'statut'      => $this->statut,

            // Counts sans charger tous les objets
            'faces_count'        => $this->faces_count        ?? $this->faces->count(),
            'faces_libres_count' => $this->faces_libres_count ?? 0,

            // Faces chargées seulement si disponibles
            'faces' => FaceResource::collection(
                $this->whenLoaded('faces')
            ),

            'created_at' => $this->created_at?->format('d/m/Y'),
            'created_at_full' => $this->created_at?->translatedFormat('d M Y'),

            'createur' => $this->whenLoaded('createur', fn () => [
                'id'   => $this->createur->id,
                'name' => $this->createur->name,
            ]),
        ];
    }
}