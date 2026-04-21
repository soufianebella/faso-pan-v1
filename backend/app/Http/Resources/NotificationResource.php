<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'type'       => $this->type,
            'titre'      => $this->titre,
            'message'    => $this->message,
            'lien'       => $this->lien,
            'lu_at'      => $this->lu_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
