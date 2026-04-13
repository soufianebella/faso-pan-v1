<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Campagne extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nom', 'annonceur', 'description',
        'date_debut', 'date_fin',
        'affiche_path', 'statut',
        // created_by : assigné manuellement, pas depuis HTTP
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin'   => 'date',
    ];

    // Relations

    public function affectations(): HasMany
    {
        return $this->hasMany(Affectation::class);
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')
                    ->select(['id', 'name', 'email']);
    }

    // Scopes

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('statut', 'active');
    }

    public function scopeExpirantDans(
        Builder $query,
        int $jours = 7
    ): Builder {
        return $query
            ->where('statut', 'active')
            ->whereBetween('date_fin', [
                now()->toDateString(),
                now()->addDays($jours)->toDateString(),
            ]);
    }
}
