<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PanneauEtat extends Model
{
    // Table d'audit — pas de updated_at
    public const UPDATED_AT = null;

    protected $table    = 'panneau_etats';
    protected $fillable = [
        'panneau_id',
        'statut_avant',
        'statut_apres',
        'motif',
        'changed_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // ── Relations ─────────────────────────────────────────────────────────────

    public function panneau(): BelongsTo
    {
        return $this->belongsTo(Panneau::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by')
                    ->select(['id', 'name']);
    }
}
