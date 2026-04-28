<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\PanneauStatus;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;


class Panneau extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'panneaux';
    protected $fillable = [
        'reference',
        'pays',
        'ville',
        'quartier',
        'adresse',
        'latitude',
        'longitude',
        'eclaire',
        'hauteur_mat',
        'statut',
        'created_by',
    ];
    protected $casts = [
        'eclaire'   => 'boolean',
        'latitude'  => 'float',
        'longitude' => 'float',
        'statut'    => PanneauStatus::class, // Cast automatique vers l'Enum
    ];

    // Relations
    public function faces(): HasMany
    {
        return $this->hasMany(Face::class);
    }

    public function etats(): HasMany
    {
        return $this->hasMany(PanneauEtat::class);
    }

      public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes (filtres réutilisables)
    public function scopeActif(Builder $query) : Builder{
        return $query->where('statut', PanneauStatus::ACTIF);
    }

    public function scopeVille(Builder $query, string $ville): Builder  {
        return $query->where('ville', $ville);
    }

    public function scopeEclaire(Builder $query, bool $eclaire = true): Builder {
        return $query->where('eclaire', $eclaire);
    }   


}
