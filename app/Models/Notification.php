<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Notification extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'notifications';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'titre',
        'message',
        'type',
        'lue',
        'destinataire_id',
        'data',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date_creation' => 'datetime',
        'lue' => 'boolean',
        'data' => 'array',
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
    }

    /**
     * Relationship: User who receives this notification
     */
    public function destinataire()
    {
        return $this->belongsTo(User::class, 'destinataire_id');
    }

    /**
     * Scope: Unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('lue', false);
    }

    /**
     * Scope: Read notifications
     */
    public function scopeRead($query)
    {
        return $query->where('lue', true);
    }

    /**
     * Scope: By type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: For specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('destinataire_id', $userId);
    }

    /**
     * Scope: Recent notifications
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('date_creation', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update(['lue' => true]);
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread()
    {
        $this->update(['lue' => false]);
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            'TACHE' => 'Tâche',
            'EVENEMENT' => 'Événement',
            'PROJET' => 'Projet',
            'SYSTEME' => 'Système',
            'RAPPORT' => 'Rapport'
        ];

        return $labels[$this->type] ?? $this->type;
    }

    /**
     * Get type color for UI
     */
    public function getTypeColorAttribute()
    {
        $colors = [
            'TACHE' => 'primary',
            'EVENEMENT' => 'info',
            'PROJET' => 'success',
            'SYSTEME' => 'warning',
            'RAPPORT' => 'secondary'
        ];

        return $colors[$this->type] ?? 'secondary';
    }

    /**
     * Get type icon for UI
     */
    public function getTypeIconAttribute()
    {
        $icons = [
            'TACHE' => 'fa-tasks',
            'EVENEMENT' => 'fa-calendar',
            'PROJET' => 'fa-project-diagram',
            'SYSTEME' => 'fa-cog',
            'RAPPORT' => 'fa-file-alt'
        ];

        return $icons[$this->type] ?? 'fa-bell';
    }

    /**
     * Get human readable time ago
     */
    public function getTimeAgoAttribute()
    {
        return $this->date_creation->diffForHumans();
    }

    /**
     * Create notification for task assignment
     */
    public static function createTaskAssigned($task, $assignee)
    {
        return self::create([
            'titre' => 'Nouvelle tâche assignée',
            'message' => "La tâche '{$task->titre}' vous a été assignée.",
            'type' => 'TACHE',
            'destinataire_id' => $assignee->id,
            'data' => [
                'task_id' => $task->id,
                'action' => 'assigned'
            ]
        ]);
    }

    /**
     * Create notification for event invitation
     */
    public static function createEventInvitation($event, $participant)
    {
        return self::create([
            'titre' => 'Invitation à un événement',
            'message' => "Vous êtes invité à l'événement '{$event->titre}' le {$event->date_debut->format('d/m/Y à H:i')}.",
            'type' => 'EVENEMENT',
            'destinataire_id' => $participant->id,
            'data' => [
                'event_id' => $event->id,
                'action' => 'invited'
            ]
        ]);
    }

    /**
     * Create notification for task deadline
     */
    public static function createTaskDeadlineReminder($task)
    {
        return self::create([
            'titre' => 'Échéance de tâche approche',
            'message' => "La tâche '{$task->titre}' arrive à échéance le {$task->date_echeance->format('d/m/Y')}.",
            'type' => 'TACHE',
            'destinataire_id' => $task->id_utilisateur,
            'data' => [
                'task_id' => $task->id,
                'action' => 'deadline_reminder'
            ]
        ]);
    }

    /**
     * Create notification for overdue task
     */
    public static function createTaskOverdue($task)
    {
        return self::create([
            'titre' => 'Tâche en retard',
            'message' => "La tâche '{$task->titre}' est en retard depuis le {$task->date_echeance->format('d/m/Y')}.",
            'type' => 'TACHE',
            'destinataire_id' => $task->id_utilisateur,
            'data' => [
                'task_id' => $task->id,
                'action' => 'overdue'
            ]
        ]);
    }

    /**
     * Create system notification
     */
    public static function createSystemNotification($title, $message, $userId = null)
    {
        return self::create([
            'titre' => $title,
            'message' => $message,
            'type' => 'SYSTEME',
            'destinataire_id' => $userId,
            'data' => [
                'action' => 'system_message'
            ]
        ]);
    }

    /**
     * Get URL for this notification
     */
    public function getUrlAttribute()
    {
        $data = $this->data ?? [];

        switch ($this->type) {
            case 'TACHE':
                if (isset($data['task_id'])) {
                    return route('tasks.show', $data['task_id']);
                }
                break;

            case 'EVENEMENT':
                if (isset($data['event_id'])) {
                    return route('events.show', $data['event_id']);
                }
                break;

            case 'PROJET':
                if (isset($data['project_id'])) {
                    return route('projects.show', $data['project_id']);
                }
                break;

            case 'RAPPORT':
                if (isset($data['report_id'])) {
                    return route('reports.show', $data['report_id']);
                }
                break;
        }

        return route('dashboard');
    }

    /**
     * Check if notification is actionable
     */
    public function isActionable()
    {
        $data = $this->data ?? [];

        return isset($data['task_id']) ||
               isset($data['event_id']) ||
               isset($data['project_id']) ||
               isset($data['report_id']);
    }
}
