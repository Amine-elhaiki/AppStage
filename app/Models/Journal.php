<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Journal extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'journal';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'date',
        'type_action',
        'description',
        'utilisateur_id',
        'adresse_ip',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date' => 'datetime',
    ];

    /**
     * Timestamps
     */
    public $timestamps = false;

    /**
     * Relationship: User who performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }

    /**
     * Scope: Entries by action type
     */
    public function scopeByActionType($query, $type)
    {
        return $query->where('type_action', $type);
    }

    /**
     * Scope: Entries by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('utilisateur_id', $userId);
    }

    /**
     * Scope: Recent entries
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('date', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Scope: Today's entries
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', Carbon::today());
    }

    /**
     * Get action type label
     */
    public function getActionTypeLabelAttribute()
    {
        $labels = [
            'CONNEXION' => 'Connexion',
            'DECONNEXION' => 'Déconnexion',
            'CREATION' => 'Création',
            'MODIFICATION' => 'Modification',
            'SUPPRESSION' => 'Suppression',
            'EXPORT' => 'Export',
            'TELECHARGEMENT' => 'Téléchargement',
            'ERREUR' => 'Erreur'
        ];

        return $labels[$this->type_action] ?? $this->type_action;
    }

    /**
     * Get action type color
     */
    public function getActionTypeColorAttribute()
    {
        $colors = [
            'CONNEXION' => 'success',
            'DECONNEXION' => 'info',
            'CREATION' => 'primary',
            'MODIFICATION' => 'warning',
            'SUPPRESSION' => 'danger',
            'EXPORT' => 'secondary',
            'TELECHARGEMENT' => 'info',
            'ERREUR' => 'danger'
        ];

        return $colors[$this->type_action] ?? 'secondary';
    }

    /**
     * Get human readable time ago
     */
    public function getTimeAgoAttribute()
    {
        return $this->date->diffForHumans();
    }
}
