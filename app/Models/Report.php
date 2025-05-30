<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Report extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'reports';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'titre',
        'date_intervention',
        'lieu',
        'type_intervention',
        'actions',
        'resultats',
        'problemes',
        'recommandations',
        'id_utilisateur',
        'id_tache',
        'id_evenement',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date_intervention' => 'date',
        'date_creation' => 'datetime',
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
     * Relationship: User who created this report
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_utilisateur');
    }

    /**
     * Relationship: Task this report is related to
     */
    public function task()
    {
        return $this->belongsTo(Task::class, 'id_tache');
    }

    /**
     * Relationship: Event this report is related to
     */
    public function event()
    {
        return $this->belongsTo(Event::class, 'id_evenement');
    }

    /**
     * Relationship: Attachments for this report
     */
    public function piecesJointes()
    {
        return $this->hasMany(PieceJointe::class, 'id_rapport');
    }

    /**
     * Scope: Reports by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('id_utilisateur', $userId);
    }

    /**
     * Scope: Reports for specific intervention type
     */
    public function scopeByInterventionType($query, $type)
    {
        return $query->where('type_intervention', 'like', "%{$type}%");
    }

    /**
     * Scope: Reports in specific location
     */
    public function scopeByLocation($query, $location)
    {
        return $query->where('lieu', 'like', "%{$location}%");
    }

    /**
     * Scope: Reports from specific date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date_intervention', [$startDate, $endDate]);
    }

    /**
     * Scope: Reports from this month
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('date_creation', Carbon::now()->month)
                    ->whereYear('date_creation', Carbon::now()->year);
    }

    /**
     * Scope: Reports from this week
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('date_creation', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    /**
     * Scope: Recent reports
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('date_creation', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Scope: Reports with problems identified
     */
    public function scopeWithProblems($query)
    {
        return $query->whereNotNull('problemes')
                    ->where('problemes', '!=', '');
    }

    /**
     * Scope: Reports with recommendations
     */
    public function scopeWithRecommendations($query)
    {
        return $query->whereNotNull('recommandations')
                    ->where('recommandations', '!=', '');
    }

    /**
     * Scope: Reports with attachments
     */
    public function scopeWithAttachments($query)
    {
        return $query->whereHas('piecesJointes');
    }

    /**
     * Check if report has problems identified
     */
    public function hasProblems()
    {
        return !empty($this->problemes);
    }

    /**
     * Check if report has recommendations
     */
    public function hasRecommendations()
    {
        return !empty($this->recommandations);
    }

    /**
     * Check if report has attachments
     */
    public function hasAttachments()
    {
        return $this->piecesJointes()->count() > 0;
    }

    /**
     * Get report age in days
     */
    public function getAgeInDaysAttribute()
    {
        return $this->date_creation->diffInDays(Carbon::now());
    }

    /**
     * Get human readable age
     */
    public function getAgeAttribute()
    {
        $days = $this->age_in_days;

        if ($days === 0) {
            return 'Aujourd\'hui';
        } elseif ($days === 1) {
            return 'Hier';
        } elseif ($days < 7) {
            return "Il y a {$days} jour(s)";
        } elseif ($days < 30) {
            $weeks = floor($days / 7);
            return "Il y a {$weeks} semaine(s)";
        } else {
            $months = floor($days / 30);
            return "Il y a {$months} mois";
        }
    }

    /**
     * Get report status based on content
     */
    public function getStatusAttribute()
    {
        if ($this->hasProblems() && $this->hasRecommendations()) {
            return 'issues_with_recommendations';
        } elseif ($this->hasProblems()) {
            return 'issues_identified';
        } elseif ($this->hasRecommendations()) {
            return 'with_recommendations';
        } else {
            return 'standard';
        }
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'issues_with_recommendations' => 'Problèmes identifiés avec recommandations',
            'issues_identified' => 'Problèmes identifiés',
            'with_recommendations' => 'Avec recommandations',
            'standard' => 'Standard'
        ];

        return $labels[$this->status] ?? 'Standard';
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'issues_with_recommendations' => 'warning',
            'issues_identified' => 'danger',
            'with_recommendations' => 'info',
            'standard' => 'success'
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Get report completeness score (0-100)
     */
    public function getCompletenessScoreAttribute()
    {
        $score = 0;
        $maxScore = 100;

        // Basic required fields (40 points)
        if (!empty($this->titre)) $score += 10;
        if (!empty($this->lieu)) $score += 10;
        if (!empty($this->type_intervention)) $score += 10;
        if (!empty($this->actions)) $score += 10;

        // Results (20 points)
        if (!empty($this->resultats)) $score += 20;

        // Additional details (40 points)
        if (!empty($this->problemes)) $score += 15;
        if (!empty($this->recommandations)) $score += 15;
        if ($this->hasAttachments()) $score += 10;

        return min($maxScore, $score);
    }

    /**
     * Get completeness level
     */
    public function getCompletenessLevelAttribute()
    {
        $score = $this->completeness_score;

        if ($score >= 90) {
            return 'excellent';
        } elseif ($score >= 70) {
            return 'good';
        } elseif ($score >= 50) {
            return 'fair';
        } else {
            return 'poor';
        }
    }

    /**
     * Get word count for actions
     */
    public function getActionsWordCountAttribute()
    {
        return str_word_count(strip_tags($this->actions ?? ''));
    }

    /**
     * Get word count for results
     */
    public function getResultsWordCountAttribute()
    {
        return str_word_count(strip_tags($this->resultats ?? ''));
    }

    /**
     * Get total word count
     */
    public function getTotalWordCountAttribute()
    {
        $content = $this->actions . ' ' . $this->resultats . ' ' .
                  $this->problemes . ' ' . $this->recommandations;

        return str_word_count(strip_tags($content));
    }

    /**
     * Get report summary (first 150 characters of actions)
     */
    public function getSummaryAttribute()
    {
        $text = strip_tags($this->actions ?? '');

        if (strlen($text) <= 150) {
            return $text;
        }

        return substr($text, 0, 147) . '...';
    }

    /**
     * Get related project through task or event
     */
    public function getRelatedProjectAttribute()
    {
        if ($this->task && $this->task->project) {
            return $this->task->project;
        }

        if ($this->event && $this->event->project) {
            return $this->event->project;
        }

        return null;
    }

    /**
     * Get intervention duration (if end time is recorded)
     */
    public function getInterventionDurationAttribute()
    {
        // This would require additional fields for start/end time
        // For now, return null or implement if needed
        return null;
    }

    /**
     * Search reports by keywords
     */
    public function scopeSearch($query, $keywords)
    {
        $keywords = '%' . $keywords . '%';

        return $query->where(function($q) use ($keywords) {
            $q->where('titre', 'like', $keywords)
              ->orWhere('lieu', 'like', $keywords)
              ->orWhere('type_intervention', 'like', $keywords)
              ->orWhere('actions', 'like', $keywords)
              ->orWhere('resultats', 'like', $keywords)
              ->orWhere('problemes', 'like', $keywords)
              ->orWhere('recommandations', 'like', $keywords);
        });
    }

    /**
     * Get reports statistics for a user
     */
    public static function getStatsForUser($userId)
    {
        $query = self::where('id_utilisateur', $userId);

        return [
            'total' => $query->count(),
            'this_month' => $query->thisMonth()->count(),
            'this_week' => $query->thisWeek()->count(),
            'with_problems' => $query->withProblems()->count(),
            'with_recommendations' => $query->withRecommendations()->count(),
            'with_attachments' => $query->withAttachments()->count(),
            'avg_completeness' => round($query->avg('completeness_score') ?? 0, 1),
        ];
    }

    /**
     * Get most common intervention types
     */
    public static function getMostCommonInterventionTypes($limit = 10)
    {
        return self::selectRaw('type_intervention, count(*) as count')
                  ->groupBy('type_intervention')
                  ->orderBy('count', 'desc')
                  ->limit($limit)
                  ->get();
    }

    /**
     * Get most common locations
     */
    public static function getMostCommonLocations($limit = 10)
    {
        return self::selectRaw('lieu, count(*) as count')
                  ->groupBy('lieu')
                  ->orderBy('count', 'desc')
                  ->limit($limit)
                  ->get();
    }

    /**
     * Check if report can be edited by given user
     */
    public function canBeEditedBy(User $user)
    {
        // Can edit within 24 hours of creation, or if admin
        $canEditTimeLimit = $this->date_creation->addHours(24) > Carbon::now();

        return $user->isAdmin() ||
               ($this->id_utilisateur === $user->id && $canEditTimeLimit);
    }

    /**
     * Check if report can be viewed by given user
     */
    public function canBeViewedBy(User $user)
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($this->id_utilisateur === $user->id) {
            return true;
        }

        // Can view if user is involved in related task or event
        if ($this->task && $this->task->canBeViewedBy($user)) {
            return true;
        }

        if ($this->event && $this->event->canBeViewedBy($user)) {
            return true;
        }

        return false;
    }

    /**
     * Generate automatic title based on content
     */
    public function generateAutoTitle()
    {
        if (!empty($this->titre)) {
            return $this->titre;
        }

        $parts = [];

        if ($this->type_intervention) {
            $parts[] = $this->type_intervention;
        }

        if ($this->lieu) {
            $parts[] = $this->lieu;
        }

        if ($this->date_intervention) {
            $parts[] = $this->date_intervention->format('d/m/Y');
        }

        return implode(' - ', $parts) ?: 'Rapport d\'intervention';
    }

    /**
     * Get priority level based on problems identified
     */
    public function getPriorityLevelAttribute()
    {
        if (!$this->hasProblems()) {
            return 'normal';
        }

        $problemsText = strtolower($this->problemes);

        // Check for urgent keywords
        $urgentKeywords = ['urgent', 'critique', 'danger', 'risque', 'panne', 'arrêt'];
        foreach ($urgentKeywords as $keyword) {
            if (strpos($problemsText, $keyword) !== false) {
                return 'high';
            }
        }

        return 'medium';
    }

    /**
     * Get priority color
     */
    public function getPriorityColorAttribute()
    {
        $colors = [
            'normal' => 'success',
            'medium' => 'warning',
            'high' => 'danger'
        ];

        return $colors[$this->priority_level] ?? 'secondary';
    }
}
