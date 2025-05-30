<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReportPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Report $report): bool
    {
        return $report->canBeViewedBy($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isActive();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Report $report): bool
    {
        return $report->canBeEditedBy($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Report $report): bool
    {
        return $user->isAdmin() ||
               ($report->id_utilisateur === $user->id &&
                $report->date_creation->diffInHours(now()) < 24);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Report $report): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Report $report): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can download attachments.
     */
    public function downloadAttachments(User $user, Report $report): bool
    {
        return $report->canBeViewedBy($user);
    }

    /**
     * Determine whether the user can add attachments.
     */
    public function addAttachments(User $user, Report $report): bool
    {
        return $report->canBeEditedBy($user);
    }

    /**
     * Determine whether the user can remove attachments.
     */
    public function removeAttachments(User $user, Report $report): bool
    {
        return $report->canBeEditedBy($user);
    }

    /**
     * Determine whether the user can export the report.
     */
    public function export(User $user, Report $report): bool
    {
        return $report->canBeViewedBy($user);
    }

    /**
     * Determine whether the user can view report statistics.
     */
    public function viewStats(User $user): bool
    {
        return $user->isActive();
    }

    /**
     * Determine whether the user can moderate reports.
     */
    public function moderate(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view all reports.
     */
    public function viewAll(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can search reports.
     */
    public function search(User $user): bool
    {
        return $user->isActive();
    }
}
