<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Tache extends Model
{
    protected $fillable = [
        'affectation_id', 'agent_id', 'statut',
        'note', 'photo_path',
        'latitude_pose', 'longitude_pose',
        'realise_at', 'valide_at', 'valide_by',
    ];

    protected $casts = [
        'realise_at' => 'datetime',
        'valide_at'  => 'datetime',
    ];

    // Relations

    public function affectation(): BelongsTo
    {
        return $this->belongsTo(Affectation::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id')
                    ->select(['id', 'name', 'email']);
    }

    public function validePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_by')
                    ->select(['id', 'name']);
    }

    // Scopes

    public function scopeEnAttente(Builder $query): Builder
    {
        return $query->where('statut', 'en_attente');
    }

    /**
     * Scope pour un agent : il ne voit QUE ses tâches
     *
     */
    public function scopePourAgent(
        Builder $query,
        int $agentId
    ): Builder {
        return $query->where('agent_id', $agentId);
    }
}
