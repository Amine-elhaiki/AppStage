<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->id === $model->id;
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
    public function update(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can change roles.
     */
    public function changeRole(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can change status (active/inactive).
     */
    public function changeStatus(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can reset passwords.
     */
    public function resetPassword(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can view user statistics.
     */
    public function viewStats(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view activity logs.
     */
    public function viewLogs(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->id === $model->id;
    }

    /**
     * Determine whether the user can manage user permissions.
     */
    public function managePermissions(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view all user data.
     */
    public function viewAllData(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can export user data.
     */
    public function export(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can impersonate other users.
     */
    public function impersonate(User $user, User $model): bool
    {
        return $user->isAdmin() &&
               $user->id !== $model->id &&
               $model->role !== 'admin';
    }
}
