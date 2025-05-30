<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
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
    public function view(User $user, Task $task): bool
    {
        return $task->canBeViewedBy($user);
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
    public function update(User $user, Task $task): bool
    {
        return $task->canBeEditedBy($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can assign the task to someone.
     */
    public function assign(User $user, Task $task): bool
    {
        return $user->isAdmin() ||
               ($task->project && $task->project->id_responsable === $user->id);
    }

    /**
     * Determine whether the user can change task status.
     */
    public function changeStatus(User $user, Task $task): bool
    {
        return $user->isAdmin() || $task->id_utilisateur === $user->id;
    }

    /**
     * Determine whether the user can update task progress.
     */
    public function updateProgress(User $user, Task $task): bool
    {
        return $user->isAdmin() || $task->id_utilisateur === $user->id;
    }

    /**
     * Determine whether the user can export tasks.
     */
    public function export(User $user): bool
    {
        return $user->isActive();
    }

    /**
     * Determine whether the user can view task statistics.
     */
    public function viewStats(User $user): bool
    {
        return $user->isActive();
    }
}
