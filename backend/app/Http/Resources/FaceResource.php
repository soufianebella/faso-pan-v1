<?php

declare(strict_types=1);

namespace App\Http\Resources;

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
            'panneau' => $this->whenLoaded('panneau', fn () => [
                'id'        => $this->panneau->id,
                'reference' => $this->panneau->reference,
                'ville'     => $this->panneau->ville,
            ]),
        ];
    }
}