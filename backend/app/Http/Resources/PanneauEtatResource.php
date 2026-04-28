<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PanneauEtatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'statut_avant' => $this->statut_avant,
            'statut_apres' => $this->statut_apres,
            'motif'        => $this->motif,
            'changed_by'   => $this->whenLoaded('changedBy', fn () => [
                'id'   => $this->changedBy->id,
                'name' => $this->changedBy->name,
            ]),
            'created_at'   => $this->created_at?->format('d/m/Y H:i'),
        ];
    }
}
