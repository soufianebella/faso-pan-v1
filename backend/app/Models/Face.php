<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Face extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'panneau_id',
        'numero',
        'largeur',
        'hauteur',
        'statut',
    ];

    protected $casts = [
        'largeur' => 'float',
        'hauteur' => 'float',
        'surface' => 'float',
    ];


    // Relations
    public function panneau(): BelongsTo
    {
        return $this->belongsTo(Panneau::class);
    }

    public function affectations(): HasMany
    {
        return $this->hasMany(Affectation::class);
    }

    /**
     * Comparaison directe sur les colonnes date
     */

    public function affectationActive(): HasOne
    {
        return $this->hasOne(Affectation::class)
            ->select([
                'id',
                'face_id',
                'campagne_id',
                'date_debut',
                'date_fin',
            ])
            ->where('date_debut', '<=', now()->toDateString())
            ->where('date_fin', '>=', now()->toDateString());
    }

    // Scopes
    public function scopeLibre(Builder $query): Builder
    {
        return $query->where('statut', 'libre');
    }
}