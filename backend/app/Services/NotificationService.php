<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public function creerPourUser(
        User   $user,
        string $type,
        string $titre,
        string $message,
        ?string $lien = null
    ): Notification {
        return Notification::create([
            'user_id' => $user->id,
            'type'    => $type,
            'titre'   => $titre,
            'message' => $message,
            'lien'    => $lien,
        ]);
    }

    public function nonLues(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return Notification::where('user_id', $user->id)
            ->whereNull('lu_at')
            ->latest()
            ->limit(20)
            ->get();
    }

    public function marquerCommeLue(Notification $notification): void
    {
        $notification->update(['lu_at' => now()]);
    }

    public function marquerToutesCommeLues(User $user): void
    {
        Notification::where('user_id', $user->id)
            ->whereNull('lu_at')
            ->update(['lu_at' => now()]);
    }

    public function compterNonLues(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->whereNull('lu_at')
            ->count();
    }
}