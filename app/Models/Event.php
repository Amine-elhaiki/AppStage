<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'description',
        'type',
        'statut',
        'date_debut',
        'date_fin',
        'lieu',
        'participants',
        'materiels_requis',
        'resultats',
        'user_id',
        'project_id',
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
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

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    /**
     * Accessors
     */
    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            'intervention' => 'Intervention',
            'reunion' => 'Réunion',
            'formation' => 'Formation',
            'visite' => 'Visite',
            'maintenance' => 'Maintenance',
            'autre' => 'Autre',
            default => $this->type
        };
    }

    public function getTypeColorAttribute()
    {
        return match($this->type) {
            'intervention' => 'danger',
            'reunion' => 'primary',
            'formation' => 'success',
            'visite' => 'info',
            'maintenance' => 'warning',
            'autre' => 'secondary',
            default => 'secondary'
        };
    }

    public function getStatusLabelAttribute()
    {
        return match($this->statut) {
            'planifie' => 'Planifié',
            'en_cours' => 'En cours',
            'termine' => 'Terminé',
            'reporte' => 'Reporté',
            'annule' => 'Annulé',
            default => $this->statut
        };
    }

    /**
     * Scopes
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date_debut', today());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date_debut', '>', now());
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Methods
     */
    public function getDurationInHours()
    {
        return $this->date_debut->diffInHours($this->date_fin);
    }

    public function isToday()
    {
        return $this->date_debut->isToday();
    }

    public function isUpcoming()
    {
        return $this->date_debut->isFuture();
    }
}

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'description',
        'type',
        'date_intervention',
        'lieu',
        'probleme_identifie',
        'actions_effectuees',
        'materiels_utilises',
        'etat_equipement',
        'recommandations',
        'cout_intervention',
        'statut',
        'photos',
        'user_id',
        'event_id',
        'project_id',
    ];

    protected $casts = [
        'date_intervention' => 'datetime',
        'cout_intervention' => 'decimal:2',
        'photos' => 'array',
    ];

    /**
     * Relations
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Accessors
     */
    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            'intervention' => 'Intervention',
            'maintenance' => 'Maintenance',
            'inspection' => 'Inspection',
            'reparation' => 'Réparation',
            'installation' => 'Installation',
            'autre' => 'Autre',
            default => $this->type
        };
    }

    public function getStatusLabelAttribute()
    {
        return match($this->statut) {
            'brouillon' => 'Brouillon',
            'soumis' => 'Soumis',
            'valide' => 'Validé',
            'rejete' => 'Rejeté',
            default => $this->statut
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->statut) {
            'brouillon' => 'secondary',
            'soumis' => 'warning',
            'valide' => 'success',
            'rejete' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Scopes
     */
    public function scopeValidated($query)
    {
        return $query->where('statut', 'valide');
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('date_intervention', now()->month)
                    ->whereYear('date_intervention', now()->year);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Methods
     */
    public function submit()
    {
        $this->update(['statut' => 'soumis']);
    }

    public function validate()
    {
        $this->update(['statut' => 'valide']);
    }

    public function reject()
    {
        $this->update(['statut' => 'rejete']);
    }

    public function hasPhotos()
    {
        return !empty($this->photos);
    }

    public function getPhotosCount()
    {
        return count($this->photos ?? []);
    }
}
