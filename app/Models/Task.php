<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'description',
        'statut',
        'priorite',
        'date_creation',
        'date_echeance',
        'date_debut_reelle',
        'date_fin_reelle',
        'progression',
        'commentaires',
        'user_id',
        'project_id',
        'created_by',
    ];

    protected $casts = [
        'date_creation' => 'datetime',
        'date_echeance' => 'datetime',
        'date_debut_reelle' => 'datetime',
        'date_fin_reelle' => 'datetime',
    ];

    /**
     * Relations
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Accessors
     */
    public function getStatusLabelAttribute()
    {
        return match($this->statut) {
            'a_faire' => 'À faire',
            'en_cours' => 'En cours',
            'termine' => 'Terminé',
            'reporte' => 'Reporté',
            'annule' => 'Annulé',
            default => $this->statut
        };
    }

    public function getPriorityLabelAttribute()
    {
        return match($this->priorite) {
            'basse' => 'Basse',
            'normale' => 'Normale',
            'haute' => 'Haute',
            'urgente' => 'Urgente',
            default => $this->priorite
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->statut) {
            'a_faire' => 'secondary',
            'en_cours' => 'primary',
            'termine' => 'success',
            'reporte' => 'warning',
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

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->whereIn('statut', ['a_faire', 'en_cours']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('statut', 'termine');
    }

    public function scopeOverdue($query)
    {
        return $query->where('statut', '!=', 'termine')
                    ->where('date_echeance', '<', now());
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priorite', $priority);
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('date_echeance', today());
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Methods
     */
    public function isOverdue()
    {
        return $this->date_echeance &&
               $this->statut !== 'termine' &&
               $this->date_echeance->isPast();
    }

    public function isDueToday()
    {
        return $this->date_echeance && $this->date_echeance->isToday();
    }

    public function isDueSoon($days = 3)
    {
        return $this->date_echeance &&
               $this->date_echeance->between(now(), now()->addDays($days));
    }

    public function start()
    {
        $this->update([
            'statut' => 'en_cours',
            'date_debut_reelle' => now(),
        ]);
    }

    public function complete()
    {
        $this->update([
            'statut' => 'termine',
            'date_fin_reelle' => now(),
            'progression' => 100,
        ]);
    }

    public function updateProgress($percentage)
    {
        $this->update(['progression' => min(100, max(0, $percentage))]);

        if ($percentage >= 100) {
            $this->complete();
        }
    }

    public function getDurationInDays()
    {
        if (!$this->date_debut_reelle || !$this->date_fin_reelle) {
            return null;
        }

        return $this->date_debut_reelle->diffInDays($this->date_fin_reelle);
    }

    public function getEstimatedDuration()
    {
        if (!$this->date_creation || !$this->date_echeance) {
            return null;
        }

        return $this->date_creation->diffInDays($this->date_echeance);
    }
}
