<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participation extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'participants_evenements';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id_evenement',
        'id_utilisateur',
        'statut_presence',
    ];

    /**
     * Relationship: Event
     */
    public function event()
    {
        return $this->belongsTo(Event::class, 'id_evenement');
    }

    /**
     * Relationship: User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_utilisateur');
    }

    /**
     * Scope: By status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('statut_presence', $status);
    }

    /**
     * Scope: Confirmed participations
     */
    public function scopeConfirmed($query)
    {
        return $query->where('statut_presence', 'confirme');
    }

    /**
     * Scope: Present participations
     */
    public function scopePresent($query)
    {
        return $query->where('statut_presence', 'present');
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'invite' => 'Invité',
            'confirme' => 'Confirmé',
            'decline' => 'Décliné',
            'present' => 'Présent',
            'absent' => 'Absent'
        ];

        return $labels[$this->statut_presence] ?? 'Inconnu';
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'invite' => 'secondary',
            'confirme' => 'primary',
            'decline' => 'danger',
            'present' => 'success',
            'absent' => 'warning'
        ];

        return $colors[$this->statut_presence] ?? 'secondary';
    }

    /**
     * Check if participation is confirmed
     */
    public function isConfirmed()
    {
        return $this->statut_presence === 'confirme';
    }

    /**
     * Check if participant was present
     */
    public function wasPresent()
    {
        return $this->statut_presence === 'present';
    }

    /**
     * Check if participation was declined
     */
    public function isDeclined()
    {
        return $this->statut_presence === 'decline';
    }
}
