<?php

namespace App\Providers;

use App\Models\Task;
use App\Models\Project;
use App\Models\Event;
use App\Models\Report;
use App\Models\User;
use App\Policies\TaskPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\EventPolicy;
use App\Policies\ReportPolicy;
use App\Policies\UserPolicy;
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
        Task::class => TaskPolicy::class,
        Project::class => ProjectPolicy::class,
        Event::class => EventPolicy::class,
        Report::class => ReportPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Gates personnalisés pour l'application PlanifTech

        // Gate pour l'accès administrateur
        Gate::define('admin-access', function ($user) {
            return $user->role === 'admin';
        });

        // Gate pour l'accès technicien
        Gate::define('technician-access', function ($user) {
            return $user->role === 'technicien' && $user->statut === 'actif';
        });

        // Gate pour la gestion des utilisateurs
        Gate::define('manage-users', function ($user) {
            return $user->role === 'admin';
        });

        // Gate pour la création de projets
        Gate::define('create-projects', function ($user) {
            return $user->role === 'admin';
        });

        // Gate pour la gestion des tâches
        Gate::define('assign-tasks', function ($user) {
            return $user->role === 'admin';
        });

        // Gate pour voir les statistiques globales
        Gate::define('view-global-stats', function ($user) {
            return $user->role === 'admin';
        });

        // Gate pour l'export de données
        Gate::define('export-data', function ($user) {
            return $user->statut === 'actif';
        });

        // Gate pour la modération de contenu
        Gate::define('moderate-content', function ($user) {
            return $user->role === 'admin';
        });

        // Gate pour l'accès aux logs système
        Gate::define('view-system-logs', function ($user) {
            return $user->role === 'admin';
        });

        // Gate pour la configuration système
        Gate::define('system-configuration', function ($user) {
            return $user->role === 'admin';
        });

        // Gate pour créer des événements
        Gate::define('create-events', function ($user) {
            return $user->statut === 'actif';
        });

        // Gate pour soumettre des rapports
        Gate::define('submit-reports', function ($user) {
            return $user->role === 'technicien' && $user->statut === 'actif';
        });

        // Gate pour voir les données d'autres utilisateurs
        Gate::define('view-others-data', function ($user, $targetUser = null) {
            if ($user->role === 'admin') {
                return true;
            }

            return $targetUser && $user->id === $targetUser->id;
        });

        // Gate pour la gestion des participations aux événements
        Gate::define('manage-event-participation', function ($user, $event) {
            return $user->role === 'admin' ||
                   $event->id_organisateur === $user->id;
        });

        // Gate pour l'édition de tâches assignées
        Gate::define('edit-assigned-task', function ($user, $task) {
            return $user->role === 'admin' ||
                   $task->id_utilisateur === $user->id ||
                   ($task->project && $task->project->id_responsable === $user->id);
        });

        // Gate pour voir les rapports sensibles
        Gate::define('view-sensitive-reports', function ($user) {
            return $user->role === 'admin';
        });

        // Gate pour la réassignation de ressources
        Gate::define('reassign-resources', function ($user) {
            return $user->role === 'admin';
        });

        // Gate pour l'accès aux API externes
        Gate::define('api-access', function ($user) {
            return $user->statut === 'actif';
        });

        // Gate pour la suppression définitive
        Gate::define('permanent-delete', function ($user) {
            return $user->role === 'admin';
        });

        // Vérification de l'utilisateur super admin (si nécessaire)
        Gate::define('super-admin', function ($user) {
            return $user->role === 'admin' && $user->email === 'admin@ormvat.ma';
        });
    }
}
