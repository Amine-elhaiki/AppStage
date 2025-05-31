<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use App\Models\Event;
use App\Models\Report;
use App\Policies\UserPolicy;
use App\Policies\TaskPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\EventPolicy;
use App\Policies\ReportPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Task::class => TaskPolicy::class,
        Project::class => ProjectPolicy::class,
        Event::class => EventPolicy::class,
        Report::class => ReportPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Gates personnalisés
        Gate::define('admin-access', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('chef-equipe-access', function (User $user) {
            return $user->isChefEquipe() || $user->isAdmin();
        });

        Gate::define('technicien-access', function (User $user) {
            return $user->isTechnicien() || $user->isChefEquipe() || $user->isAdmin();
        });

        Gate::define('manage-users', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('validate-reports', function (User $user) {
            return $user->isAdmin() || $user->isChefEquipe();
        });

        Gate::define('manage-system', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('view-analytics', function (User $user) {
            return $user->isAdmin() || $user->isChefEquipe();
        });

        Gate::define('export-data', function (User $user) {
            return $user->isAdmin() || $user->isChefEquipe();
        });

        // Gate pour vérifier les permissions spécifiques
        Gate::define('has-permission', function (User $user, string $permission) {
            if ($user->isAdmin()) {
                return true; // Les admins ont toutes les permissions
            }

            return $user->hasPermission($permission);
        });

        // Gates pour les actions sur les projets
        Gate::define('create-project', function (User $user) {
            return $user->isAdmin() || $user->isChefEquipe();
        });

        Gate::define('manage-project', function (User $user, Project $project) {
            return $user->isAdmin() || $project->responsable_id === $user->id;
        });

        // Gates pour les tâches
        Gate::define('create-task', function (User $user) {
            return $user->isAdmin() || $user->isChefEquipe();
        });

        Gate::define('assign-task', function (User $user) {
            return $user->isAdmin() || $user->isChefEquipe();
        });

        // Gates pour les rapports
        Gate::define('validate-report', function (User $user) {
            return $user->isAdmin() || $user->isChefEquipe();
        });

        Gate::define('view-all-reports', function (User $user) {
            return $user->isAdmin() || $user->isChefEquipe();
        });

        // Gates pour l'administration
        Gate::define('access-admin-panel', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-settings', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('view-system-logs', function (User $user) {
            return $user->isAdmin();
        });

        // Gates pour les statistiques et rapports
        Gate::define('view-global-stats', function (User $user) {
            return $user->isAdmin() || $user->isChefEquipe();
        });

        Gate::define('export-reports', function (User $user) {
            return $user->isAdmin() || $user->isChefEquipe();
        });

        // Gate pour les actions en masse
        Gate::define('bulk-actions', function (User $user) {
            return $user->isAdmin();
        });

        // Gates spécifiques pour certaines fonctionnalités
        Gate::define('emergency-access', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('backup-system', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('impersonate-user', function (User $user) {
            return $user->isAdmin();
        });
    }
}
