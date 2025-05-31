<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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
        'telephone',
        'specialite',
        'statut',
        'permissions',
        'derniere_connexion',
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
        'derniere_connexion' => 'datetime',
        'permissions' => 'array',
        'password' => 'hashed',
    ];

    /**
     * Relations
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function createdTasks()
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'responsable_id');
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    /**
     * Accessors
     */
    public function getInitialsAttribute()
    {
        return strtoupper(substr($this->prenom, 0, 1) . substr($this->nom, 0, 1));
    }

    public function getFullNameAttribute()
    {
        return $this->prenom . ' ' . $this->nom;
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Methods
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isTechnicien()
    {
        return $this->role === 'technicien';
    }

    public function isChefEquipe()
    {
        return $this->role === 'chef_equipe';
    }

    public function hasPermission($permission)
    {
        if ($this->isAdmin()) {
            return true;
        }

        return in_array($permission, $this->permissions ?? []);
    }

    public function updateLastLogin()
    {
        $this->update(['derniere_connexion' => now()]);
    }

    public function getActiveTasks()
    {
        return $this->tasks()->whereIn('statut', ['a_faire', 'en_cours']);
    }

    public function getCompletedTasksCount()
    {
        return $this->tasks()->where('statut', 'termine')->count();
    }

    public function getOverdueTasksCount()
    {
        return $this->tasks()
            ->where('statut', '!=', 'termine')
            ->where('date_echeance', '<', now())
            ->count();
    }

    public function getTodayEvents()
    {
        return $this->events()
            ->whereDate('date_debut', today())
            ->orderBy('date_debut');
    }

    public function getRecentReports($limit = 5)
    {
        return $this->reports()
            ->orderBy('created_at', 'desc')
            ->limit($limit);
    }
}
