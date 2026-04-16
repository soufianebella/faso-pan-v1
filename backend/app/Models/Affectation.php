<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

class Affectation extends Model
{
    use HasFactory;

    protected $fillable = [
        'campagne_id', 'face_id',
        'date_debut', 'date_fin',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin'   => 'date',
    ];

    // Relations

    public function campagne(): BelongsTo
    {
        return $this->belongsTo(Campagne::class)
                    ->select(['id', 'nom', 'annonceur', 'statut']);
    }

    public function face(): BelongsTo
    {
        return $this->belongsTo(Face::class)
                    ->select(['id', 'panneau_id', 'numero', 'statut']);
    }

    public function tache(): HasOne
    {
        return $this->hasOne(Tache::class);
    }

    // Scopes

    /**
     * Le scope de chevauchement : anti double-booking
     */
    public function scopeChevauche(
        Builder $query,
        string  $debut,
        string  $fin
    ): Builder {
        return $query
            ->where('date_debut', '<=', $fin)
            ->where('date_fin',   '>=', $debut);
    }
}
