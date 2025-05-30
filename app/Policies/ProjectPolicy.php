<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
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
    public function view(User $user, Project $project): bool
    {
        return $project->canBeViewedBy($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        return $project->canBeEditedBy($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can manage project tasks.
     */
    public function manageTasks(User $user, Project $project): bool
    {
        return $user->isAdmin() || $project->id_responsable === $user->id;
    }

    /**
     * Determine whether the user can view project reports.
     */
    public function viewReports(User $user, Project $project): bool
    {
        return $project->canBeViewedBy($user);
    }

    /**
     * Determine whether the user can change project status.
     */
    public function changeStatus(User $user, Project $project): bool
    {
        return $user->isAdmin() || $project->id_responsable === $user->id;
    }

    /**
     * Determine whether the user can assign team members.
     */
    public function assignMembers(User $user, Project $project): bool
    {
        return $user->isAdmin() || $project->id_responsable === $user->id;
    }

    /**
     * Determine whether the user can generate reports.
     */
    public function generateReports(User $user, Project $project): bool
    {
        return $project->canBeViewedBy($user);
    }
}
