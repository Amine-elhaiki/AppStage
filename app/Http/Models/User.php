<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;


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
        'telephone',
        'role',
        'statut',
        'password',
        'dernier_connexion',
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
        'password' => 'hashed',
        'date_creation' => 'datetime',
        'date_modification' => 'datetime',
        'dernier_connexion' => 'datetime',
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
     * Get the table associated with the model.
     */
    public function getTable()
    {
        return 'users';
    }

    /**
     * Relationship: Tasks assigned to this user
     */
    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'id_utilisateur');
    }

    /**
     * Relationship: Projects managed by this user
     */
    public function managedProjects()
    {
        return $this->hasMany(Project::class, 'id_responsable');
    }

    /**
     * Relationship: Events organized by this user
     */
    public function organizedEvents()
    {
        return $this->hasMany(Event::class, 'id_organisateur');
    }

    /**
     * Relationship: Events where this user participates
     */
    public function participatedEvents()
    {
        return $this->belongsToMany(Event::class, 'participants_evenements', 'id_utilisateur', 'id_evenement')
                    ->withPivot('statut_presence')
                    ->withTimestamps();
    }

    /**
     * Relationship: Reports created by this user
     */
    public function reports()
    {
        return $this->hasMany(Report::class, 'id_utilisateur');
    }

    /**
     * Relationship: Journal entries for this user
     */
    public function journalEntries()
    {
        return $this->hasMany(Journal::class, 'utilisateur_id');
    }

    /**
     * Relationship: Notifications for this user
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'destinataire_id');
    }

    /**
     * Scope: Active users only
     */
    public function scopeActive($query)
    {
        return $query->where('statut', 'actif');
    }

    /**
     * Scope: Users by role
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope: Technicians only
     */
    public function scopeTechnicians($query)
    {
        return $query->where('role', 'technicien');
    }

    /**
     * Scope: Administrators only
     */
    public function scopeAdministrators($query)
    {
        return $query->where('role', 'admin');
    }

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute()
    {
        return $this->nom . ' ' . $this->prenom;
    }

    /**
     * Get initials attribute
     */
    public function getInitialsAttribute()
    {
        return strtoupper(substr($this->nom, 0, 1) . substr($this->prenom, 0, 1));
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is technician
     */
    public function isTechnician()
    {
        return $this->role === 'technicien';
    }

    /**
     * Check if user is active
     */
    public function isActive()
    {
        return $this->statut === 'actif';
    }

    /**
     * Get user's pending tasks count
     */
    public function getPendingTasksCountAttribute()
    {
        return $this->assignedTasks()->whereIn('statut', ['a_faire', 'en_cours'])->count();
    }

    /**
     * Get user's completed tasks count
     */
    public function getCompletedTasksCountAttribute()
    {
        return $this->assignedTasks()->where('statut', 'termine')->count();
    }

    /**
     * Get user's overdue tasks count
     */
    public function getOverdueTasksCountAttribute()
    {
        return $this->assignedTasks()
                    ->where('date_echeance', '<', Carbon::today())
                    ->whereIn('statut', ['a_faire', 'en_cours'])
                    ->count();
    }

    /**
     * Get user's reports count for current month
     */
    public function getMonthlyReportsCountAttribute()
    {
        return $this->reports()
                    ->whereMonth('date_creation', Carbon::now()->month)
                    ->whereYear('date_creation', Carbon::now()->year)
                    ->count();
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin()
    {
        $this->dernier_connexion = now();
        $this->save();
    }

    /**
     * Get user's upcoming events
     */
    public function getUpcomingEvents($limit = 5)
    {
        return $this->participatedEvents()
                    ->where('date_debut', '>', Carbon::now())
                    ->orderBy('date_debut')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Get user's tasks for today
     */
    public function getTodayTasks()
    {
        return $this->assignedTasks()
                    ->whereDate('date_echeance', Carbon::today())
                    ->whereIn('statut', ['a_faire', 'en_cours'])
                    ->orderBy('priorite', 'desc')
                    ->get();
    }

    /**
     * Get user's priority tasks
     */
    public function getPriorityTasks($limit = 10)
    {
        return $this->assignedTasks()
                    ->where('priorite', 'haute')
                    ->whereIn('statut', ['a_faire', 'en_cours'])
                    ->orderBy('date_echeance')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Get user's task completion rate
     */
    public function getTaskCompletionRate()
    {
        $totalTasks = $this->assignedTasks()->count();

        if ($totalTasks === 0) {
            return 0;
        }

        $completedTasks = $this->assignedTasks()->where('statut', 'termine')->count();

        return round(($completedTasks / $totalTasks) * 100, 2);
    }

    /**
     * Get user's average task completion time in days
     */
    public function getAverageTaskCompletionTime()
    {
        $completedTasks = $this->assignedTasks()
                              ->where('statut', 'termine')
                              ->whereNotNull('date_modification')
                              ->get();

        if ($completedTasks->isEmpty()) {
            return 0;
        }

        $totalDays = 0;
        $count = 0;

        foreach ($completedTasks as $task) {
            $createdAt = Carbon::parse($task->date_creation);
            $completedAt = Carbon::parse($task->date_modification);
            $totalDays += $createdAt->diffInDays($completedAt);
            $count++;
        }

        return $count > 0 ? round($totalDays / $count, 1) : 0;
    }

    /**
     * Get user's workload for current week
     */
    public function getWeeklyWorkload()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        return [
            'tasks_due_this_week' => $this->assignedTasks()
                                         ->whereBetween('date_echeance', [$startOfWeek, $endOfWeek])
                                         ->whereIn('statut', ['a_faire', 'en_cours'])
                                         ->count(),
            'events_this_week' => $this->participatedEvents()
                                      ->whereBetween('date_debut', [$startOfWeek, $endOfWeek])
                                      ->count(),
        ];
    }

    /**
     * Check if user can manage projects
     */
    public function canManageProjects()
    {
        return $this->isAdmin() || $this->managedProjects()->exists();
    }

    /**
     * Check if user can create events
     */
    public function canCreateEvents()
    {
        return $this->isActive();
    }

    /**
     * Check if user can view all data (admin privileges)
     */
    public function canViewAllData()
    {
        return $this->isAdmin();
    }

    /**
     * Get user's productivity score (0-100)
     */
    public function getProductivityScore()
    {
        $completionRate = $this->getTaskCompletionRate();
        $monthlyReports = $this->monthly_reports_count;
        $overdueTasksCount = $this->overdue_tasks_count;
        $totalTasks = $this->assigned_tasks_count;

        // Calculate base score from completion rate
        $score = $completionRate * 0.4;

        // Add points for monthly reports (max 20 points)
        $score += min($monthlyReports * 4, 20);

        // Add points for task management (max 20 points)
        if ($totalTasks > 0) {
            $onTimeRate = (($totalTasks - $overdueTasksCount) / $totalTasks) * 100;
            $score += $onTimeRate * 0.2;
        }

        // Ensure score is between 0 and 100
        return max(0, min(100, round($score)));
    }
}
