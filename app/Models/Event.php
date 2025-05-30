<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'events';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'titre',
        'description',
        'type',
        'date_debut',
        'date_fin',
        'lieu',
        'coordonnees_gps',
        'statut',
        'priorite',
        'id_organisateur',
        'id_projet',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
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
     * Relationship: User who organizes this event
     */
    public function organisateur()
    {
        return $this->belongsTo(User::class, 'id_organisateur');
    }

    /**
     * Relationship: Project this event belongs to
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'id_projet');
    }

    /**
     * Relationship: Tasks associated with this event
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'id_evenement');
    }

    /**
     * Relationship: Reports related to this event
     */
    public function reports()
    {
        return $this->hasMany(Report::class, 'id_evenement');
    }

    /**
     * Relationship: Event participants
     */
    public function participants()
    {
        return $this->hasMany(Participation::class, 'id_evenement');
    }

    /**
     * Relationship: Users participating in this event
     */
    public function participantUsers()
    {
        return $this->belongsToMany(User::class, 'participants_evenements', 'id_evenement', 'id_utilisateur')
                    ->withPivot('statut_presence')
                    ->withTimestamps();
    }

    /**
     * Scope: Events by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Events by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('statut', $status);
    }

    /**
     * Scope: Upcoming events
     */
    public function scopeUpcoming($query)
    {
        return $query->where('date_debut', '>', Carbon::now())
                    ->whereIn('statut', ['planifie']);
    }

    /**
     * Scope: Past events
     */
    public function scopePast($query)
    {
        return $query->where('date_fin', '<', Carbon::now());
    }

    /**
     * Scope: Today's events
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date_debut', Carbon::today());
    }

    /**
     * Scope: This week's events
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('date_debut', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    /**
     * Scope: Events organized by specific user
     */
    public function scopeOrganizedBy($query, $userId)
    {
        return $query->where('id_organisateur', $userId);
    }

    /**
     * Scope: Events in specific project
     */
    public function scopeInProject($query, $projectId)
    {
        return $query->where('id_projet', $projectId);
    }

    /**
     * Scope: High priority events
     */
    public function scopeHighPriority($query)
    {
        return $query->where('priorite', 'urgente');
    }

    /**
     * Check if event is upcoming
     */
    public function isUpcoming()
    {
        return $this->date_debut > Carbon::now() && $this->statut === 'planifie';
    }

    /**
     * Check if event is happening now
     */
    public function isHappeningNow()
    {
        $now = Carbon::now();
        return $this->date_debut <= $now &&
               $this->date_fin >= $now &&
               $this->statut === 'en_cours';
    }

    /**
     * Check if event is past
     */
    public function isPast()
    {
        return $this->date_fin < Carbon::now();
    }

    /**
     * Check if event is today
     */
    public function isToday()
    {
        return $this->date_debut->isToday();
    }

    /**
     * Check if event is this week
     */
    public function isThisWeek()
    {
        return $this->date_debut->between(
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        );
    }

    /**
     * Check if event is cancelled
     */
    public function isCancelled()
    {
        return $this->statut === 'annule';
    }

    /**
     * Check if event is postponed
     */
    public function isPostponed()
    {
        return $this->statut === 'reporte';
    }

    /**
     * Get event type label
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            'intervention' => 'Intervention technique',
            'reunion' => 'Réunion',
            'formation' => 'Formation',
            'visite' => 'Visite de terrain'
        ];

        return $labels[$this->type] ?? 'Autre';
    }

    /**
     * Get event status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'planifie' => 'Planifié',
            'en_cours' => 'En cours',
            'termine' => 'Terminé',
            'annule' => 'Annulé',
            'reporte' => 'Reporté'
        ];

        return $labels[$this->statut] ?? 'Inconnu';
    }

    /**
     * Get priority label
     */
    public function getPriorityLabelAttribute()
    {
        $labels = [
            'normale' => 'Normale',
            'haute' => 'Haute',
            'urgente' => 'Urgente'
        ];

        return $labels[$this->priorite] ?? 'Normale';
    }

    /**
     * Get type color class for UI
     */
    public function getTypeColorAttribute()
    {
        $colors = [
            'intervention' => 'danger',
            'reunion' => 'primary',
            'formation' => 'success',
            'visite' => 'warning'
        ];

        return $colors[$this->type] ?? 'secondary';
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
            'annule' => 'danger',
            'reporte' => 'warning'
        ];

        return $colors[$this->statut] ?? 'secondary';
    }

    /**
     * Get priority color class for UI
     */
    public function getPriorityColorAttribute()
    {
        $colors = [
            'normale' => 'success',
            'haute' => 'warning',
            'urgente' => 'danger'
        ];

        return $colors[$this->priorite] ?? 'secondary';
    }

    /**
     * Get event duration in hours
     */
    public function getDurationInHoursAttribute()
    {
        return $this->date_debut->diffInHours($this->date_fin);
    }

    /**
     * Get event duration in minutes
     */
    public function getDurationInMinutesAttribute()
    {
        return $this->date_debut->diffInMinutes($this->date_fin);
    }

    /**
     * Get human readable duration
     */
    public function getFormattedDurationAttribute()
    {
        $hours = $this->duration_in_hours;
        $minutes = $this->duration_in_minutes % 60;

        if ($hours === 0) {
            return $minutes . ' minute(s)';
        } elseif ($minutes === 0) {
            return $hours . ' heure(s)';
        } else {
            return $hours . 'h' . $minutes . 'min';
        }
    }

    /**
     * Get time until event starts
     */
    public function getTimeUntilStartAttribute()
    {
        if ($this->isPast()) {
            return 'Événement passé';
        }

        if ($this->isHappeningNow()) {
            return 'En cours maintenant';
        }

        $now = Carbon::now();
        $hoursUntil = $now->diffInHours($this->date_debut);
        $minutesUntil = $now->diffInMinutes($this->date_debut) % 60;

        if ($hoursUntil === 0) {
            return 'Dans ' . $minutesUntil . ' minute(s)';
        } elseif ($hoursUntil < 24) {
            return 'Dans ' . $hoursUntil . 'h' . ($minutesUntil > 0 ? $minutesUntil . 'min' : '');
        } else {
            $days = $now->diffInDays($this->date_debut);
            return 'Dans ' . $days . ' jour(s)';
        }
    }

    /**
     * Get participation statistics
     */
    public function getParticipationStatsAttribute()
    {
        $totalParticipants = $this->participants()->count();

        if ($totalParticipants === 0) {
            return [
                'total' => 0,
                'confirmed' => 0,
                'declined' => 0,
                'pending' => 0,
                'present' => 0,
                'absent' => 0
            ];
        }

        return [
            'total' => $totalParticipants,
            'confirmed' => $this->participants()->where('statut_presence', 'confirme')->count(),
            'declined' => $this->participants()->where('statut_presence', 'decline')->count(),
            'pending' => $this->participants()->where('statut_presence', 'invite')->count(),
            'present' => $this->participants()->where('statut_presence', 'present')->count(),
            'absent' => $this->participants()->where('statut_presence', 'absent')->count(),
        ];
    }

    /**
     * Get attendance rate (present/total confirmed)
     */
    public function getAttendanceRateAttribute()
    {
        $stats = $this->participation_stats;
        $confirmed = $stats['confirmed'];

        if ($confirmed === 0) {
            return 0;
        }

        return round(($stats['present'] / $confirmed) * 100, 1);
    }

    /**
     * Get confirmation rate (confirmed/total invited)
     */
    public function getConfirmationRateAttribute()
    {
        $stats = $this->participation_stats;
        $total = $stats['total'];

        if ($total === 0) {
            return 0;
        }

        return round(($stats['confirmed'] / $total) * 100, 1);
    }

    /**
     * Add participant to event
     */
    public function addParticipant($userId, $status = 'invite')
    {
        return Participation::firstOrCreate([
            'id_evenement' => $this->id,
            'id_utilisateur' => $userId,
        ], [
            'statut_presence' => $status
        ]);
    }

    /**
     * Remove participant from event
     */
    public function removeParticipant($userId)
    {
        return Participation::where('id_evenement', $this->id)
                           ->where('id_utilisateur', $userId)
                           ->delete();
    }

    /**
     * Update participant status
     */
    public function updateParticipantStatus($userId, $status)
    {
        return Participation::where('id_evenement', $this->id)
                           ->where('id_utilisateur', $userId)
                           ->update(['statut_presence' => $status]);
    }

    /**
     * Check if user is participant
     */
    public function hasParticipant($userId)
    {
        return $this->participants()->where('id_utilisateur', $userId)->exists();
    }

    /**
     * Get participant status for specific user
     */
    public function getParticipantStatus($userId)
    {
        $participation = $this->participants()->where('id_utilisateur', $userId)->first();
        return $participation ? $participation->statut_presence : null;
    }

    /**
     * Auto-start event if it's time
     */
    public function autoStartIfTime()
    {
        if ($this->statut === 'planifie' &&
            $this->date_debut <= Carbon::now() &&
            $this->date_fin > Carbon::now()) {

            $this->statut = 'en_cours';
            $this->save();

            return true;
        }

        return false;
    }

    /**
     * Auto-end event if time is up
     */
    public function autoEndIfTime()
    {
        if ($this->statut === 'en_cours' && $this->date_fin <= Carbon::now()) {
            $this->statut = 'termine';
            $this->save();

            return true;
        }

        return false;
    }

    /**
     * Check if event conflicts with another event for a user
     */
    public function hasConflictForUser($userId)
    {
        return Event::where('id', '!=', $this->id)
                   ->whereHas('participants', function($q) use ($userId) {
                       $q->where('id_utilisateur', $userId)
                         ->where('statut_presence', 'confirme');
                   })
                   ->where(function($q) {
                       $q->whereBetween('date_debut', [$this->date_debut, $this->date_fin])
                         ->orWhereBetween('date_fin', [$this->date_debut, $this->date_fin])
                         ->orWhere(function($q2) {
                             $q2->where('date_debut', '<=', $this->date_debut)
                                ->where('date_fin', '>=', $this->date_fin);
                         });
                   })
                   ->exists();
    }

    /**
     * Get conflicting events for organizer
     */
    public function getConflictingEventsAttribute()
    {
        return Event::where('id', '!=', $this->id)
                   ->where('id_organisateur', $this->id_organisateur)
                   ->where(function($q) {
                       $q->whereBetween('date_debut', [$this->date_debut, $this->date_fin])
                         ->orWhereBetween('date_fin', [$this->date_debut, $this->date_fin])
                         ->orWhere(function($q2) {
                             $q2->where('date_debut', '<=', $this->date_debut)
                                ->where('date_fin', '>=', $this->date_fin);
                         });
                   })
                   ->get();
    }

    /**
     * Check if event can be edited by given user
     */
    public function canBeEditedBy(User $user)
    {
        return $user->isAdmin() || $this->id_organisateur === $user->id;
    }

    /**
     * Check if event can be viewed by given user
     */
    public function canBeViewedBy(User $user)
    {
        return $user->isAdmin() ||
               $this->id_organisateur === $user->id ||
               $this->hasParticipant($user->id);
    }

    /**
     * Get reminder notifications schedule
     */
    public function getReminderScheduleAttribute()
    {
        $reminders = collect();

        // 1 day before
        $oneDayBefore = $this->date_debut->subDay();
        if ($oneDayBefore > Carbon::now()) {
            $reminders->push([
                'time' => $oneDayBefore,
                'message' => 'Rappel: Événement demain - ' . $this->titre
            ]);
        }

        // 1 hour before
        $oneHourBefore = $this->date_debut->subHour();
        if ($oneHourBefore > Carbon::now()) {
            $reminders->push([
                'time' => $oneHourBefore,
                'message' => 'Rappel: Événement dans 1 heure - ' . $this->titre
            ]);
        }

        return $reminders;
    }
}
