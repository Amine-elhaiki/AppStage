<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        // Les admins et chefs d'équipe peuvent voir tous les projets
        if ($user->isAdmin() || $user->isChefEquipe()) {
            return true;
        }

        // Les techniciens peuvent voir les projets où ils ont des tâches
        return $project->tasks()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isChefEquipe();
    }

    public function update(User $user, Project $project): bool
    {
        // Les admins peuvent modifier tous les projets
        if ($user->isAdmin()) {
            return true;
        }

        // Les chefs d'équipe peuvent modifier les projets dont ils sont responsables
        if ($user->isChefEquipe()) {
            return $project->responsable_id === $user->id;
        }

        return false;
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }

    public function manage(User $user, Project $project): bool
    {
        return $user->isAdmin() || $project->responsable_id === $user->id;
    }
}

// ===== REPORT POLICY =====

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Report $report): bool
    {
        // Les admins et chefs d'équipe peuvent voir tous les rapports
        if ($user->isAdmin() || $user->isChefEquipe()) {
            return true;
        }

        // Les techniciens peuvent voir leurs propres rapports
        return $report->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        // Tous les utilisateurs authentifiés peuvent créer des rapports
        return true;
    }

    public function update(User $user, Report $report): bool
    {
        // Les admins peuvent modifier tous les rapports
        if ($user->isAdmin()) {
            return true;
        }

        // Les auteurs peuvent modifier leurs rapports s'ils ne sont pas validés
        if ($report->user_id === $user->id) {
            return in_array($report->statut, ['brouillon', 'rejete']);
        }

        return false;
    }

    public function delete(User $user, Report $report): bool
    {
        // Les admins peuvent supprimer tous les rapports
        if ($user->isAdmin()) {
            return true;
        }

        // Les auteurs peuvent supprimer leurs rapports s'ils sont en brouillon
        return $report->user_id === $user->id && $report->statut === 'brouillon';
    }

    public function validate(User $user, Report $report): bool
    {
        return $user->isAdmin() || $user->isChefEquipe();
    }

    public function submit(User $user, Report $report): bool
    {
        return $report->user_id === $user->id && $report->statut === 'brouillon';
    }
}

// ===== USER POLICY =====

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $model): bool
    {
        // Les utilisateurs peuvent voir leur propre profil
        if ($user->id === $model->id) {
            return true;
        }

        // Les admins peuvent voir tous les profils
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $model): bool
    {
        // Les utilisateurs peuvent modifier leur propre profil (limité)
        if ($user->id === $model->id) {
            return true;
        }

        // Les admins peuvent modifier tous les profils
        return $user->isAdmin();
    }

    public function delete(User $user, User $model): bool
    {
        // Impossible de se supprimer soi-même
        if ($user->id === $model->id) {
            return false;
        }

        // Seuls les admins peuvent supprimer
        return $user->isAdmin();
    }

    public function manageRole(User $user, User $model): bool
    {
        // Seuls les admins peuvent gérer les rôles
        return $user->isAdmin() && $user->id !== $model->id;
    }

    public function resetPassword(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->id !== $model->id;
    }

    public function toggleStatus(User $user, User $model): bool
    {
        // Impossible de modifier son propre statut
        if ($user->id === $model->id) {
            return false;
        }

        return $user->isAdmin();
    }
}

// ===== EVENT POLICY =====

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Event $event): bool
    {
        // Les admins et chefs d'équipe peuvent voir tous les événements
        if ($user->isAdmin() || $user->isChefEquipe()) {
            return true;
        }

        // Les techniciens peuvent voir leurs propres événements
        return $event->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        // Tous les utilisateurs authentifiés peuvent créer des événements
        return true;
    }

    public function update(User $user, Event $event): bool
    {
        // Les admins peuvent modifier tous les événements
        if ($user->isAdmin()) {
            return true;
        }

        // Les chefs d'équipe peuvent modifier les événements de leur équipe
        if ($user->isChefEquipe()) {
            return true; // À adapter selon la logique d'équipe
        }

        // Les propriétaires peuvent modifier leurs événements
        return $event->user_id === $user->id;
    }

    public function delete(User $user, Event $event): bool
    {
        // Les admins peuvent supprimer tous les événements
        if ($user->isAdmin()) {
            return true;
        }

        // Les propriétaires peuvent supprimer leurs événements s'ils ne sont pas commencés
        if ($event->user_id === $user->id) {
            return $event->statut === 'planifie';
        }

        return false;
    }

    public function cancel(User $user, Event $event): bool
    {
        // Les admins et chefs d'équipe peuvent annuler les événements
        if ($user->isAdmin() || $user->isChefEquipe()) {
            return true;
        }

        // Les propriétaires peuvent annuler leurs événements
        return $event->user_id === $user->id && $event->statut === 'planifie';
    }
}
