<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory;
    use HasApiTokens;  //  Sanctum : génère et vérifie les tokens
    use HasRoles;      //  Spatie : $user->hasRole(), $user->can()
    use SoftDeletes;   //  deleted_at : jamais de suppression physique


    protected $fillable = [
        'name',
        'email',
        'password',
        'actif',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed', // Hash auto à l'assignation
        'actif'             => 'boolean',
    ];

    // Relations


    public function panneaux(): HasMany
    {
        return $this->hasMany(Panneau::class, 'created_by');
    }

    /**
     * Tâches assignées à cet agent terrain
     */
    public function taches(): HasMany
    {
        return $this->hasMany(Tache::class, 'agent_id');
    }

    /**
     * Notifications de cet utilisateur
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Notifications non lues (relation séparée pour les counts)
     *
     *  Pourquoi une relation séparée ?
     *    $user->notificationsNonLues()->count()
     *    = 1 requête SQL ciblée avec WHERE lu_at IS NULL
     * */

    public function notificationsNonLues(): HasMany
    {
        return $this->hasMany(Notification::class)
                    ->whereNull('lu_at');
    }

    //Scopes

    /**
     * Usage : User::actif()->get()
     *    Jamais ->where('actif', true) dispersé dans les controllers
     */
    public function scopeActif(Builder $query): Builder
    {
        return $query->where('actif', true);
    }

    /**
     *  Usage : User::role('agent_terrain')->get()
     *    Spatie fournit déjà ->role() mais on le redéfinit
     *    pour être explicite dans notre codebase
     */
    public function scopeParRole(Builder $query, string $role): Builder
    {
        return $query->role($role); // méthode Spatie
    }

    // Helpers métier

  
    public function estSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function estGestionnaire(): bool
    {
        return $this->hasRole('gestionnaire');
    }

    public function estAgent(): bool
    {
        return $this->hasRole('agent_terrain');
    }

    /**
     *  Vérifie si le compte peut se connecter
     *    Utilisé dans AuthController avant d'émettre le token
     */
    public function peutSeConnecter(): bool
    {
        return $this->actif === true;
    }
}
