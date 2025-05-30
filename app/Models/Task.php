<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Task extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'tasks';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'titre',
        'description',
        'date_echeance',
        'priorite',
        'statut',
        'progression',
        'id_utilisateur',
        'id_projet',
        'id_evenement',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date_echeance' => 'date',
        'date_creation' => 'datetime',
        'date_modification' => 'datetime',
        'progression' => 'integer',
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
     * Relationship: User assigned to this task
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_utilisateur');
    }

    /**
     * Relationship: Project this task belongs to
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'id_projet');
    }

    /**
     * Relationship: Event this task is associated with
     */
    public function event()
    {
        return $this->belongsTo(Event::class, 'id_evenement');
    }

    /**
     * Relationship: Reports related to this task
     */
    public function reports()
    {
        return $this->hasMany(Report::class, 'id_tache');
    }

    /**
     * Scope: Tasks by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('statut', $status);
    }

    /**
     * Scope: Tasks by priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priorite', $priority);
    }

    /**
     * Scope: Pending tasks (à faire or en cours)
     */
    public function scopePending($query)
    {
        return $query->whereIn('statut', ['a_faire', 'en_cours']);
    }

    /**
     * Scope: Completed tasks
     */
    public function scopeCompleted($query)
    {
        return $query->where('statut', 'termine');
    }

    /**
     * Scope: Overdue tasks
     */
    public function scopeOverdue($query)
    {
        return $query->where('date_echeance', '<', Carbon::today())
                    ->whereIn('statut', ['a_faire', 'en_cours']);
    }

    /**
     * Scope: High priority tasks
     */
    public function scopeHighPriority($query)
    {
        return $query->where('priorite', 'haute');
    }

    /**
     * Scope: Tasks due today
     */
    public function scopeDueToday($query)
    {
        return $query->whereDate('date_echeance', Carbon::today());
    }

    /**
     * Scope: Tasks due this week
     */
    public function scopeDueThisWeek($query)
    {
        return $query->whereBetween('date_echeance', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    /**
     * Scope: Tasks assigned to specific user
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('id_utilisateur', $userId);
    }

    /**
     * Scope: Tasks in specific project
     */
    public function scopeInProject($query, $projectId)
    {
        return $query->where('id_projet', $projectId);
    }

    /**
     * Check if task is overdue
     */
    public function isOverdue()
    {
        return $this->date_echeance < Carbon::today() &&
               in_array($this->statut, ['a_faire', 'en_cours']);
    }

    /**
     * Check if task is due today
     */
    public function isDueToday()
    {
        return $this->date_echeance->isToday() &&
               in_array($this->statut, ['a_faire', 'en_cours']);
    }

    /**
     * Check if task is due this week
     */
    public function isDueThisWeek()
    {
        return $this->date_echeance->between(
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ) && in_array($this->statut, ['a_faire', 'en_cours']);
    }

    /**
     * Check if task is completed
     */
    public function isCompleted()
    {
        return $this->statut === 'termine';
    }

    /**
     * Check if task is in progress
     */
    public function isInProgress()
    {
        return $this->statut === 'en_cours';
    }

    /**
     * Check if task is pending
     */
    public function isPending()
    {
        return $this->statut === 'a_faire';
    }

    /**
     * Check if task is high priority
     */
    public function isHighPriority()
    {
        return $this->priorite === 'haute';
    }

    /**
     * Get priority label
     */
    public function getPriorityLabelAttribute()
    {
        $labels = [
            'basse' => 'Basse',
            'moyenne' => 'Moyenne',
            'haute' => 'Haute'
        ];

        return $labels[$this->priorite] ?? 'Inconnue';
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'a_faire' => 'À faire',
            'en_cours' => 'En cours',
            'termine' => 'Terminé'
        ];

        return $labels[$this->statut] ?? 'Inconnu';
    }

    /**
     * Get priority color class for UI
     */
    public function getPriorityColorAttribute()
    {
        $colors = [
            'basse' => 'success',
            'moyenne' => 'warning',
            'haute' => 'danger'
        ];

        return $colors[$this->priorite] ?? 'secondary';
    }

    /**
     * Get status color class for UI
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'a_faire' => 'secondary',
            'en_cours' => 'primary',
            'termine' => 'success'
        ];

        if ($this->isOverdue()) {
            return 'danger';
        }

        return $colors[$this->statut] ?? 'secondary';
    }

    /**
     * Get days remaining until due date
     */
    public function getDaysRemainingAttribute()
    {
        if ($this->isCompleted()) {
            return 0;
        }

        $today = Carbon::today();
        $dueDate = $this->date_echeance;

        if ($dueDate < $today) {
            return -$today->diffInDays($dueDate); // Negative for overdue
        }

        return $today->diffInDays($dueDate);
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
            return 'Échéance aujourd\'hui';
        } elseif ($daysRemaining === 1) {
            return 'Échéance demain';
        } else {
            return 'Échéance dans ' . $daysRemaining . ' jour(s)';
        }
    }

    /**
     * Get estimated completion date based on current progression
     */
    public function getEstimatedCompletionAttribute()
    {
        if ($this->isCompleted()) {
            return $this->date_modification;
        }

        if ($this->progression <= 0) {
            return null;
        }

        $daysElapsed = Carbon::parse($this->date_creation)->diffInDays(Carbon::now());
        $progressRate = $this->progression / 100;

        if ($progressRate > 0) {
            $estimatedTotalDays = $daysElapsed / $progressRate;
            $remainingDays = $estimatedTotalDays - $daysElapsed;

            return Carbon::now()->addDays(max(0, $remainingDays));
        }

        return null;
    }

    /**
     * Get progress percentage with automatic calculation for certain statuses
     */
    public function getProgressPercentageAttribute()
    {
        switch ($this->statut) {
            case 'a_faire':
                return max(0, $this->progression);
            case 'en_cours':
                return max(1, $this->progression); // At least 1% for in-progress
            case 'termine':
                return 100;
            default:
                return $this->progression;
        }
    }

    /**
     * Update task progress and status automatically
     */
    public function updateProgress($percentage)
    {
        $this->progression = max(0, min(100, $percentage));

        // Auto-update status based on progress
        if ($this->progression >= 100) {
            $this->statut = 'termine';
        } elseif ($this->progression > 0 && $this->statut === 'a_faire') {
            $this->statut = 'en_cours';
        }

        $this->save();
    }

    /**
     * Mark task as started
     */
    public function markAsStarted()
    {
        $this->statut = 'en_cours';
        if ($this->progression <= 0) {
            $this->progression = 1;
        }
        $this->save();
    }

    /**
     * Mark task as completed
     */
    public function markAsCompleted()
    {
        $this->statut = 'termine';
        $this->progression = 100;
        $this->save();
    }

    /**
     * Get task urgency level (0-3)
     * 0: Normal, 1: Important, 2: Urgent, 3: Critical
     */
    public function getUrgencyLevelAttribute()
    {
        $urgency = 0;

        // Priority adds to urgency
        if ($this->priorite === 'haute') {
            $urgency += 2;
        } elseif ($this->priorite === 'moyenne') {
            $urgency += 1;
        }

        // Due date adds urgency
        $daysRemaining = $this->days_remaining;
        if ($daysRemaining < 0) {
            $urgency += 2; // Overdue
        } elseif ($daysRemaining <= 1) {
            $urgency += 1; // Due today or tomorrow
        }

        return min(3, $urgency);
    }

    /**
     * Get urgency label
     */
    public function getUrgencyLabelAttribute()
    {
        $labels = [
            0 => 'Normal',
            1 => 'Important',
            2 => 'Urgent',
            3 => 'Critique'
        ];

        return $labels[$this->urgency_level] ?? 'Normal';
    }

    /**
     * Scope: Order by urgency (most urgent first)
     */
    public function scopeOrderByUrgency($query)
    {
        return $query->orderByRaw('
            CASE
                WHEN date_echeance < CURDATE() AND statut IN ("a_faire", "en_cours") THEN 4
                WHEN date_echeance = CURDATE() AND statut IN ("a_faire", "en_cours") THEN 3
                WHEN priorite = "haute" THEN 2
                WHEN priorite = "moyenne" THEN 1
                ELSE 0
            END DESC
        ')->orderBy('date_echeance', 'asc');
    }

    /**
     * Check if task can be edited by given user
     */
    public function canBeEditedBy(User $user)
    {
        return $user->isAdmin() || $this->id_utilisateur === $user->id;
    }

    /**
     * Check if task can be viewed by given user
     */
    public function canBeViewedBy(User $user)
    {
        return $user->isAdmin() ||
               $this->id_utilisateur === $user->id ||
               ($this->project && $this->project->id_responsable === $user->id);
    }
}
