<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    // HasApiTokens complètement supprimé - Pas nécessaire pour PlanifTech

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'role',
        'statut',
        'telephone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        // 'password' => 'hashed', // Supprimé - Peut causer des problèmes selon version Laravel
    ];

    /**
     * Les timestamps sont activés (created_at, updated_at)
     */
    public $timestamps = true;

    /**
     * Noms des colonnes timestamps (standard Laravel)
     */
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Boot function pour définir les événements du modèle
     */
    protected static function boot()
    {
        parent::boot();

        // Événement lors de la création d'un utilisateur
        static::creating(function ($user) {
            if (empty($user->statut)) {
                $user->statut = 'actif';
            }
        });
    }

    /**
     * Vérifier si l'utilisateur est administrateur
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Vérifier si l'utilisateur est technicien
     */
    public function isTechnicien(): bool
    {
        return $this->role === 'technicien';
    }

    /**
     * Vérifier si l'utilisateur est actif
     */
    public function isActive(): bool
    {
        return $this->statut === 'actif';
    }

    /**
     * Obtenir le nom complet de l'utilisateur
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->prenom} {$this->nom}");
    }

    /**
     * Accessor pour formater le nom complet (alternative)
     */
    public function getNomCompletAttribute(): string
    {
        return $this->getFullNameAttribute();
    }

    /**
     * Relations avec les tâches assignées à cet utilisateur
     */
    public function taches()
    {
        return $this->hasMany(Task::class, 'id_utilisateur');
    }

    /**
     * Relations avec les projets dont l'utilisateur est responsable
     */
    public function projetsResponsable()
    {
        return $this->hasMany(Project::class, 'id_responsable');
    }

    /**
     * Relations avec les événements organisés par cet utilisateur
     */
    public function evenementsOrganises()
    {
        return $this->hasMany(Event::class, 'id_organisateur');
    }

    /**
     * Relations avec les événements auxquels l'utilisateur participe
     */
    public function evenementsParticipation()
    {
        return $this->belongsToMany(Event::class, 'participants_evenements', 'id_utilisateur', 'id_evenement')
                    ->withPivot('statut_presence')
                    ->withTimestamps();
    }

    /**
     * Relations avec les rapports créés par cet utilisateur
     */
    public function rapports()
    {
        return $this->hasMany(Report::class, 'id_utilisateur');
    }

    /**
     * Scope pour filtrer les utilisateurs actifs
     */
    public function scopeActive($query)
    {
        return $query->where('statut', 'actif');
    }

    /**
     * Scope pour filtrer par rôle
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope pour les administrateurs uniquement
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    /**
     * Scope pour les techniciens uniquement
     */
    public function scopeTechniciens($query)
    {
        return $query->where('role', 'technicien');
    }

    /**
     * Compter les tâches en cours pour cet utilisateur
     */
    public function getTachesEnCoursCount()
    {
        return $this->taches()->where('statut', 'en_cours')->count();
    }

    /**
     * Compter les tâches en retard pour cet utilisateur
     */
    public function getTachesEnRetardCount()
    {
        return $this->taches()
                    ->where('statut', '!=', 'termine')
                    ->where('date_echeance', '<', now())
                    ->count();
    }

    /**
     * Obtenir les derniers rapports de cet utilisateur
     */
    public function getDerniersRapports($limit = 5)
    {
        return $this->rapports()
                    ->orderBy('date_creation', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Mutator pour hasher automatiquement le mot de passe
     * (Alternative au cast 'password' => 'hashed' qui peut poser problème)
     */
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = bcrypt($value);
        }
    }

    /**
     * Méthodes utilitaires pour PlanifTech
     */

    /**
     * Obtenir les statistiques de l'utilisateur
     */
    public function getStatistiques()
    {
        return [
            'taches_total' => $this->taches()->count(),
            'taches_terminees' => $this->taches()->where('statut', 'termine')->count(),
            'taches_en_cours' => $this->getTachesEnCoursCount(),
            'taches_en_retard' => $this->getTachesEnRetardCount(),
            'rapports_total' => $this->rapports()->count(),
            'projets_responsable' => $this->projetsResponsable()->count(),
            'evenements_organises' => $this->evenementsOrganises()->count(),
        ];
    }

    /**
     * Vérifier si l'utilisateur peut modifier un élément
     */
    public function canModify($item)
    {
        // Admin peut tout modifier
        if ($this->isAdmin()) {
            return true;
        }

        // Technicien ne peut modifier que ses propres éléments
        if (isset($item->id_utilisateur)) {
            return $item->id_utilisateur === $this->id;
        }

        return false;
    }

    /**
     * Obtenir le rôle formaté en français
     */
    public function getRoleFormatte()
    {
        return match($this->role) {
            'admin' => 'Administrateur',
            'technicien' => 'Technicien',
            default => 'Non défini'
        };
    }

    /**
     * Obtenir le statut formaté en français
     */
    public function getStatutFormatte()
    {
        return match($this->statut) {
            'actif' => 'Actif',
            'inactif' => 'Inactif',
            default => 'Non défini'
        };
    }
}
