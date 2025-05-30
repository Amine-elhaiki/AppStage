<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Project extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'projects';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nom',
        'description',
        'date_debut',
        'date_fin',
        'zone_geographique',
        'id_responsable',
        'statut',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'date_creation' => 'datetime',
        'date_modification' => 'datetime',
    ];

    /**
     * Boot method for the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->date_creation = now();
        });

        static::updating(function ($model) {
            $model->date_modification = now();
        });
    }

    /**
     * Relationship: User responsible for this project
     */
    public function responsable()
    {
        return $this->belongsTo(User::class, 'id_responsable');
    }

    /**
     * Relationship: Tasks in this project
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'id_projet');
    }

    /**
     * Relationship: Events in this project
     */
    public function events()
    {
        return $this->hasMany(Event::class, 'id_projet');
    }

    /**
     * Relationship: Count of completed tasks
     */
    public function completedTasks()
    {
        return $this->hasMany(Task::class, 'id_projet')->where('statut', 'termine');
    }

    /**
     * Relationship: Count of pending tasks
     */
    public function pendingTasks()
    {
        return $this->hasMany(Task::class, 'id_projet')->whereIn('statut', ['a_faire', 'en_cours']);
    }

    /**
     * Relationship: Overdue tasks in this project
     */
    public function overdueTasks()
    {
        return $this->hasMany(Task::class, 'id_projet')
                    ->where('date_echeance', '<', Carbon::today())
                    ->whereIn('statut', ['a_faire', 'en_cours']);
    }

    /**
     * Scope: Projects by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('statut', $status);
    }

    /**
     * Scope: Active projects
     */
    public function scopeActive($query)
    {
        return $query->whereIn('statut', ['planifie', 'en_cours']);
    }

    /**
     * Scope: Completed projects
     */
    public function scopeCompleted($query)
    {
        return $query->where('statut', 'termine');
    }

    /**
     * Scope: Projects managed by specific user
     */
    public function scopeManagedBy($query, $userId)
    {
        return $query->where('id_responsable', $userId);
    }

    /**
     * Scope: Projects in specific zone
     */
    public function scopeInZone($query, $zone)
    {
        return $query->where('zone_geographique', 'like', "%{$zone}%");
    }

    /**
     * Scope: Projects due this month
     */
    public function scopeDueThisMonth($query)
    {
        return $query->whereMonth('date_fin', Carbon::now()->month)
                    ->whereYear('date_fin', Carbon::now()->year);
    }

    /**
     * Scope: Overdue projects
     */
    public function scopeOverdue($query)
    {
        return $query->where('date_fin', '<', Carbon::today())
                    ->whereIn('statut', ['planifie', 'en_cours']);
    }

    /**
     * Check if project is active
     */
    public function isActive()
    {
        return in_array($this->statut, ['planifie', 'en_cours']);
    }

    /**
     * Check if project is completed
     */
    public function isCompleted()
    {
        return $this->statut === 'termine';
    }

    /**
     * Check if project is overdue
     */
    public function isOverdue()
    {
        return $this->date_fin < Carbon::today() &&
               in_array($this->statut, ['planifie', 'en_cours']);
    }

    /**
     * Check if project is starting soon (within 7 days)
     */
    public function isStartingSoon()
    {
        return $this->date_debut->between(Carbon::today(), Carbon::today()->addDays(7)) &&
               $this->statut === 'planifie';
    }

    /**
     * Check if project is ending soon (within 14 days)
     */
    public function isEndingSoon()
    {
        return $this->date_fin->between(Carbon::today(), Carbon::today()->addDays(14)) &&
               in_array($this->statut, ['planifie', 'en_cours']);
    }

    /**
     * Get project status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'planifie' => 'Planifié',
            'en_cours' => 'En cours',
            'termine' => 'Terminé',
            'suspendu' => 'Suspendu'
        ];

        return $labels[$this->statut] ?? 'Inconnu';
    }

    /**
     * Get status color class for UI
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'planifie' => 'info',
            'en_cours' => 'primary',
            'termine' => 'success',
            'suspendu' => 'warning'
        ];

        if ($this->isOverdue()) {
            return 'danger';
        }

        return $colors[$this->statut] ?? 'secondary';
    }

    /**
     * Calculate project progress percentage
     */
    public function getProgressPercentageAttribute()
    {
        $totalTasks = $this->tasks()->count();

        if ($totalTasks === 0) {
            // Base progress on dates if no tasks
            if ($this->statut === 'termine') {
                return 100;
            }

            $totalDays = $this->date_debut->diffInDays($this->date_fin);
            $daysPassed = max(0, $this->date_debut->diffInDays(Carbon::now()));

            if ($totalDays > 0) {
                return min(100, round(($daysPassed / $totalDays) * 100));
            }

            return 0;
        }

        $completedTasks = $this->tasks()->where('statut', 'termine')->count();

        return round(($completedTasks / $totalTasks) * 100);
    }

    /**
     * Get detailed progress breakdown
     */
    public function getProgressBreakdownAttribute()
    {
        $totalTasks = $this->tasks()->count();

        if ($totalTasks === 0) {
            return [
                'total' => 0,
                'completed' => 0,
                'in_progress' => 0,
                'pending' => 0,
                'overdue' => 0,
                'percentage' => $this->progress_percentage
            ];
        }

        $completed = $this->tasks()->where('statut', 'termine')->count();
        $inProgress = $this->tasks()->where('statut', 'en_cours')->count();
        $pending = $this->tasks()->where('statut', 'a_faire')->count();
        $overdue = $this->overdueTasks()->count();

        return [
            'total' => $totalTasks,
            'completed' => $completed,
            'in_progress' => $inProgress,
            'pending' => $pending,
            'overdue' => $overdue,
            'percentage' => round(($completed / $totalTasks) * 100)
        ];
    }

    /**
     * Get project duration in days
     */
    public function getDurationInDaysAttribute()
    {
        return $this->date_debut->diffInDays($this->date_fin);
    }

    /**
     * Get days remaining until end date
     */
    public function getDaysRemainingAttribute()
    {
        if ($this->isCompleted()) {
            return 0;
        }

        $today = Carbon::today();

        if ($this->date_fin < $today) {
            return -$today->diffInDays($this->date_fin); // Negative for overdue
        }

        return $today->diffInDays($this->date_fin);
    }

    /**
     * Get human readable time remaining
     */
    public function getTimeRemainingAttribute()
    {
        if ($this->isCompleted()) {
            return 'Terminé';
        }

        $daysRemaining = $this->days_remaining;

        if ($daysRemaining < 0) {
            return 'En retard de ' . abs($daysRemaining) . ' jour(s)';
        } elseif ($daysRemaining === 0) {
            return 'Se termine aujourd\'hui';
        } elseif ($daysRemaining === 1) {
            return 'Se termine demain';
        } else {
            return 'Se termine dans ' . $daysRemaining . ' jour(s)';
        }
    }

    /**
     * Get project health status
     * Returns: 'excellent', 'good', 'warning', 'critical'
     */
    public function getHealthStatusAttribute()
    {
        if ($this->isCompleted()) {
            return 'excellent';
        }

        $progress = $this->progress_percentage;
        $overdueTasks = $this->overdueTasks()->count();
        $totalTasks = $this->tasks()->count();
        $daysRemaining = $this->days_remaining;

        // Critical conditions
        if ($this->isOverdue() || $overdueTasks > ($totalTasks * 0.3)) {
            return 'critical';
        }

        // Warning conditions
        if ($daysRemaining <= 7 && $progress < 80) {
            return 'warning';
        }

        if ($overdueTasks > 0 || ($daysRemaining <= 14 && $progress < 60)) {
            return 'warning';
        }

        // Good conditions
        if ($progress >= 75) {
            return 'excellent';
        }

        return 'good';
    }

    /**
     * Get team members (users with tasks in this project)
     */
    public function getTeamMembersAttribute()
    {
        return User::whereIn('id', $this->tasks()->pluck('id_utilisateur'))->get();
    }

    /**
     * Get most active team member
     */
    public function getMostActiveTeamMemberAttribute()
    {
        return $this->tasks()
                   ->selectRaw('id_utilisateur, count(*) as task_count')
                   ->groupBy('id_utilisateur')
                   ->orderBy('task_count', 'desc')
                   ->with('user')
                   ->first()?->user;
    }

    /**
     * Get next milestone (next task due date)
     */
    public function getNextMilestoneAttribute()
    {
        return $this->tasks()
                   ->whereIn('statut', ['a_faire', 'en_cours'])
                   ->orderBy('date_echeance')
                   ->first();
    }

    /**
     * Get project timeline events
     */
    public function getTimelineEventsAttribute()
    {
        $events = collect();

        // Project start
        $events->push([
            'date' => $this->date_debut,
            'type' => 'project_start',
            'title' => 'Début du projet',
            'description' => $this->nom
        ]);

        // Task deadlines
        $this->tasks()->orderBy('date_echeance')->get()->each(function($task) use ($events) {
            $events->push([
                'date' => $task->date_echeance,
                'type' => 'task_deadline',
                'title' => $task->titre,
                'description' => 'Échéance de tâche',
                'status' => $task->statut,
                'priority' => $task->priorite
            ]);
        });

        // Project end
        $events->push([
            'date' => $this->date_fin,
            'type' => 'project_end',
            'title' => 'Fin prévue du projet',
            'description' => $this->nom
        ]);

        return $events->sortBy('date');
    }

    /**
     * Calculate estimated completion date based on current progress
     */
    public function getEstimatedCompletionDateAttribute()
    {
        if ($this->isCompleted()) {
            return $this->date_modification;
        }

        $progress = $this->progress_percentage;

        if ($progress <= 0) {
            return $this->date_fin; // Original end date if no progress
        }

        $daysElapsed = $this->date_debut->diffInDays(Carbon::now());
        $progressRate = $progress / 100;

        if ($progressRate > 0) {
            $estimatedTotalDays = $daysElapsed / $progressRate;
            $remainingDays = $estimatedTotalDays - $daysElapsed;

            return $this->date_debut->addDays(max(0, $estimatedTotalDays));
        }

        return $this->date_fin;
    }

    /**
     * Check if project can be edited by given user
     */
    public function canBeEditedBy(User $user)
    {
        return $user->isAdmin() || $this->id_responsable === $user->id;
    }

    /**
     * Check if project can be viewed by given user
     */
    public function canBeViewedBy(User $user)
    {
        return $user->isAdmin() ||
               $this->id_responsable === $user->id ||
               $this->tasks()->where('id_utilisateur', $user->id)->exists();
    }

    /**
     * Auto-update project status based on tasks
     */
    public function updateStatusBasedOnTasks()
    {
        $totalTasks = $this->tasks()->count();

        if ($totalTasks === 0) {
            return; // No automatic status change if no tasks
        }

        $completedTasks = $this->tasks()->where('statut', 'termine')->count();
        $inProgressTasks = $this->tasks()->where('statut', 'en_cours')->count();

        if ($completedTasks === $totalTasks) {
            $this->statut = 'termine';
        } elseif ($inProgressTasks > 0 || $completedTasks > 0) {
            $this->statut = 'en_cours';
        }

        $this->save();
    }
}
