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
        // Tous les utilisateurs authentifiés peuvent voir la liste des tâches
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        // Les admins et chefs d'équipe peuvent voir toutes les tâches
        if ($user->isAdmin() || $user->isChefEquipe()) {
            return true;
        }

        // Les techniciens peuvent voir leurs propres tâches ou celles qu'ils ont créées
        return $task->user_id === $user->id || $task->created_by === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Seuls les admins et chefs d'équipe peuvent créer des tâches
        return $user->isAdmin() || $user->isChefEquipe();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        // Les admins peuvent modifier toutes les tâches
        if ($user->isAdmin()) {
            return true;
        }

        // Les chefs d'équipe peuvent modifier les tâches de leur équipe
        if ($user->isChefEquipe()) {
            return true; // À adapter selon la logique d'équipe
        }

        // Les techniciens peuvent modifier leurs propres tâches
        if ($user->isTechnicien()) {
            return $task->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        // Seuls les admins peuvent supprimer des tâches
        if ($user->isAdmin()) {
            return true;
        }

        // Les chefs d'équipe peuvent supprimer les tâches qu'ils ont créées
        if ($user->isChefEquipe() && $task->created_by === $user->id) {
            return true;
        }

        return false;
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
     * Determine whether the user can assign the task to others.
     */
    public function assign(User $user, Task $task): bool
    {
        // Seuls les admins et chefs d'équipe peuvent assigner des tâches
        return $user->isAdmin() || $user->isChefEquipe();
    }

    /**
     * Determine whether the user can change task priority.
     */
    public function changePriority(User $user, Task $task): bool
    {
        // Les admins peuvent changer la priorité de toutes les tâches
        if ($user->isAdmin()) {
            return true;
        }

        // Les chefs d'équipe peuvent changer la priorité des tâches de leur équipe
        if ($user->isChefEquipe()) {
            return true;
        }

        // Les techniciens ne peuvent pas changer la priorité de leurs tâches
        return false;
    }

    /**
     * Determine whether the user can mark task as complete.
     */
    public function complete(User $user, Task $task): bool
    {
        // L'utilisateur assigné peut marquer sa tâche comme terminée
        return $task->user_id === $user->id || $user->isAdmin() || $user->isChefEquipe();
    }

    /**
     * Determine whether the user can reopen a completed task.
     */
    public function reopen(User $user, Task $task): bool
    {
        // Seuls les admins et chefs d'équipe peuvent rouvrir une tâche terminée
        return $user->isAdmin() || $user->isChefEquipe();
    }
}
