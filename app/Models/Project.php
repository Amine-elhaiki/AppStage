<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'description',
        'statut',
        'priorite',
        'date_debut',
        'date_fin',
        'budget',
        'zone_geographique',
        'pourcentage_avancement',
        'responsable_id',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'budget' => 'decimal:2',
    ];

    /**
     * Relations
     */
    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
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
    public function getStatusLabelAttribute()
    {
        return match($this->statut) {
            'planifie' => 'Planifié',
            'en_cours' => 'En cours',
            'suspendu' => 'Suspendu',
            'termine' => 'Terminé',
            'annule' => 'Annulé',
            default => $this->statut
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->statut) {
            'planifie' => 'secondary',
            'en_cours' => 'primary',
            'suspendu' => 'warning',
            'termine' => 'success',
            'annule' => 'danger',
            default => 'secondary'
        };
    }

    public function getPriorityColorAttribute()
    {
        return match($this->priorite) {
            'basse' => 'success',
            'normale' => 'info',
            'haute' => 'warning',
            'urgente' => 'danger',
            default => 'secondary'
        };
    }

    public function getTasksCountAttribute()
    {
        return $this->tasks()->count();
    }

    public function getCompletedTasksCountAttribute()
    {
        return $this->tasks()->where('statut', 'termine')->count();
    }

    public function getProgressPercentageAttribute()
    {
        $totalTasks = $this->tasks_count;
        if ($totalTasks === 0) {
            return $this->pourcentage_avancement;
        }

        return round(($this->completed_tasks_count / $totalTasks) * 100);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->whereIn('statut', ['planifie', 'en_cours']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('statut', 'termine');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('responsable_id', $userId);
    }

    public function scopeByZone($query, $zone)
    {
        return $query->where('zone_geographique', $zone);
    }

    public function scopeOverdue($query)
    {
        return $query->where('statut', '!=', 'termine')
                    ->where('date_fin', '<', now());
    }

    /**
     * Methods
     */
    public function isOverdue()
    {
        return $this->statut !== 'termine' &&
               $this->date_fin &&
               $this->date_fin->isPast();
    }

    public function getDurationInDays()
    {
        if (!$this->date_debut || !$this->date_fin) {
            return null;
        }

        return $this->date_debut->diffInDays($this->date_fin);
    }

    public function getRemainingDays()
    {
        if (!$this->date_fin || $this->statut === 'termine') {
            return null;
        }

        return now()->diffInDays($this->date_fin, false);
    }

    public function updateProgress()
    {
        $totalTasks = $this->tasks()->count();

        if ($totalTasks === 0) {
            return;
        }

        $completedTasks = $this->tasks()->where('statut', 'termine')->count();
        $percentage = round(($completedTasks / $totalTasks) * 100);

        $this->update(['pourcentage_avancement' => $percentage]);

        // Auto-completion du projet si toutes les tâches sont terminées
        if ($percentage === 100 && $this->statut !== 'termine') {
            $this->update(['statut' => 'termine']);
        }
    }

    public function addTask(array $taskData)
    {
        $taskData['project_id'] = $this->id;
        $taskData['date_creation'] = now();

        return Task::create($taskData);
    }

    public function getTeamMembers()
    {
        return User::whereIn('id',
            $this->tasks()->distinct()->pluck('user_id')
        )->get();
    }

    public function getBudgetUsed()
    {
        return $this->reports()
            ->whereNotNull('cout_intervention')
            ->sum('cout_intervention');
    }

    public function getBudgetRemaining()
    {
        if (!$this->budget) {
            return null;
        }

        return $this->budget - $this->getBudgetUsed();
    }
}
