<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id', 'type', 'titre',
        'message', 'lien', 'lu_at',
    ];

    protected $casts = [
        'lu_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scope : non lues seulement
    public function scopeNonLues($query)
    {
        return $query->whereNull('lu_at');
    }
}
