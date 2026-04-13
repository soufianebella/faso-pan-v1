<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'email' => $this->email,
           
            // getRoleNames() retourne une Collection
            //    ->first() = le rôle principal (on en a qu'un par user)
            'role'  => $this->getRoleNames()->first(),

            'actif' => $this->actif,

            // $this->when() = champ inclus SEULEMENT si la
            //    condition est vraie. Évite d'exposer le count
            //    à tous les utilisateurs
            'notifications_non_lues' => $this->when(
                $request->routeIs('me'),
                fn() => $this->notificationsNonLues()->count()
            ),
        ];
    }
}
