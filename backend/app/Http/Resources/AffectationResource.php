<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AffectationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'campagne_id' => $this->campagne_id,
            'face_id'     => $this->face_id,
            'date_debut'  => $this->date_debut,
            'date_fin'    => $this->date_fin,
            'campagne'    => $this->whenLoaded('campagne'),
            'face'        => $this->whenLoaded('face'),
        ];
    }
}
