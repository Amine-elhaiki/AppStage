<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use App\Models\Event;
use App\Models\Report;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Dashboard général
     */
    public function index()
    {
        $user = auth()->user();

        // Statistiques personnelles
        $stats = [
            'my_tasks' => $user->tasks()->whereIn('statut', ['a_faire', 'en_cours'])->count(),
            'completed_tasks' => $user->getCompletedTasksCount(),
            'overdue_tasks' => $user->getOverdueTasksCount(),
            'today_events' => $user->getTodayEvents()->count(),
            'active_projects' => Project::active()->count(),
            'recent_reports' => Report::thisMonth()->count(),
        ];

        // Tâches prioritaires
        $priority_tasks = $user->tasks()
            ->whereIn('priorite', ['haute', 'urgente'])
            ->whereIn('statut', ['a_faire', 'en_cours'])
            ->with(['project'])
            ->orderBy('date_echeance')
            ->limit(5)
            ->get();

        // Projets actifs
        $active_projects = Project::active()
            ->with(['responsable'])
            ->withCount(['tasks', 'tasks as completed_tasks_count' => function($query) {
                $query->where('statut', 'termine');
            }])
            ->orderBy('date_fin')
            ->limit(3)
            ->get();

        // Événements du jour
        $today_events = $user->getTodayEvents()
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'stats',
            'priority_tasks',
            'active_projects',
            'today_events'
        ));
    }

    /**
     * Dashboard administrateur
     */
    public function admin()
    {
        // Statistiques générales
        $stats = [
            'total_users' => User::active()->count(),
            'active_projects' => Project::active()->count(),
            'total_projects' => Project::count(),
            'in_progress_tasks' => Task::where('statut', 'en_cours')->count(),
            'overdue_tasks' => Task::overdue()->count(),
            'today_events' => Event::today()->count(),
            'week_reports' => Report::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
        ];

        // Tâches prioritaires globales
        $priority_tasks = Task::with(['user', 'project'])
            ->whereIn('priorite', ['haute', 'urgente'])
            ->whereIn('statut', ['a_faire', 'en_cours'])
            ->orderBy('date_echeance')
            ->limit(10)
            ->get();

        // Événements du jour
        $today_events = Event::today()
            ->with(['user'])
            ->orderBy('date_debut')
            ->get();

        // Projets actifs
        $active_projects = Project::active()
            ->with(['responsable'])
            ->withCount(['tasks', 'tasks as completed_tasks_count' => function($query) {
                $query->where('statut', 'termine');
            }])
            ->orderBy('pourcentage_avancement', 'desc')
            ->limit(5)
            ->get();

        // Utilisateurs actifs
        $active_users = User::active()
            ->withCount(['tasks as assigned_tasks_count' => function($query) {
                $query->whereIn('statut', ['a_faire', 'en_cours']);
            }])
            ->orderBy('assigned_tasks_count', 'desc')
            ->limit(8)
            ->get();

        return view('dashboard.admin', compact(
            'stats',
            'priority_tasks',
            'today_events',
            'active_projects',
            'active_users'
        ));
    }

    /**
     * Dashboard technicien
     */
    public function technicien()
    {
        $user = auth()->user();

        // Statistiques personnelles
        $stats = [
            'my_tasks' => $user->tasks()->whereIn('statut', ['a_faire', 'en_cours'])->count(),
            'pending_tasks' => $user->tasks()->where('statut', 'a_faire')->count(),
            'completed_tasks' => $user->getCompletedTasksCount(),
            'overdue_tasks' => $user->getOverdueTasksCount(),
            'my_events_today' => $user->getTodayEvents()->count(),
            'my_reports_week' => $user->reports()->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
        ];

        // Mes tâches prioritaires
        $my_priority_tasks = $user->tasks()
            ->whereIn('priorite', ['haute', 'urgente'])
            ->whereIn('statut', ['a_faire', 'en_cours'])
            ->with(['project'])
            ->orderBy('date_echeance')
            ->limit(8)
            ->get();

        // Mes événements du jour
        $my_today_events = $user->getTodayEvents()
            ->limit(6)
            ->get();

        // Mes projets
        $my_projects = Project::whereHas('tasks', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['responsable'])
            ->withCount(['tasks', 'tasks as completed_tasks_count' => function($query) {
                $query->where('statut', 'termine');
            }])
            ->orderBy('pourcentage_avancement', 'desc')
            ->limit(4)
            ->get();

        // Mes rapports récents
        $my_recent_reports = $user->getRecentReports(5)->get();

        return view('dashboard.technicien', compact(
            'stats',
            'my_priority_tasks',
            'my_today_events',
            'my_projects',
            'my_recent_reports'
        ));
    }

    /**
     * Dashboard chef d'équipe
     */
    public function chefEquipe()
    {
        $user = auth()->user();

        // Équipe sous sa responsabilité (exemple basique)
        $team_members = User::where('role', 'technicien')
            ->active()
            ->limit(10)
            ->get();

        // Statistiques de l'équipe
        $team_stats = [
            'team_size' => $team_members->count(),
            'team_tasks' => Task::whereIn('user_id', $team_members->pluck('id'))
                ->whereIn('statut', ['a_faire', 'en_cours'])
                ->count(),
            'team_completed_tasks' => Task::whereIn('user_id', $team_members->pluck('id'))
                ->where('statut', 'termine')
                ->whereMonth('updated_at', now()->month)
                ->count(),
            'team_overdue_tasks' => Task::whereIn('user_id', $team_members->pluck('id'))
                ->overdue()
                ->count(),
        ];

        // Projets sous supervision
        $supervised_projects = Project::whereIn('responsable_id', $team_members->pluck('id'))
            ->orWhere('responsable_id', $user->id)
            ->active()
            ->with(['responsable'])
            ->withCount(['tasks', 'tasks as completed_tasks_count' => function($query) {
                $query->where('statut', 'termine');
            }])
            ->get();

        return view('dashboard.chef-equipe', compact(
            'team_stats',
            'team_members',
            'supervised_projects'
        ));
    }

    /**
     * API pour les données du dashboard
     */
    public function apiData()
    {
        $user = auth()->user();

        $data = [
            'user' => [
                'id' => $user->id,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'role' => $user->role,
            ],
            'stats' => [
                'tasks' => $user->tasks()->whereIn('statut', ['a_faire', 'en_cours'])->count(),
                'events_today' => $user->getTodayEvents()->count(),
                'reports_week' => $user->reports()->whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->count(),
            ],
            'notifications' => [],
            'timestamp' => now()->toISOString(),
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Statistiques rapides pour les widgets
     */
    public function quickStats()
    {
        $user = auth()->user();

        $stats = [
            'tasks' => [
                'total' => $user->tasks()->count(),
                'pending' => $user->tasks()->where('statut', 'a_faire')->count(),
                'in_progress' => $user->tasks()->where('statut', 'en_cours')->count(),
                'completed' => $user->tasks()->where('statut', 'termine')->count(),
                'overdue' => $user->getOverdueTasksCount(),
            ],
            'events' => [
                'today' => $user->getTodayEvents()->count(),
                'this_week' => $user->events()->whereBetween('date_debut', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->count(),
            ],
            'reports' => [
                'this_month' => $user->reports()->thisMonth()->count(),
                'validated' => $user->reports()->where('statut', 'valide')->count(),
            ]
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Graphiques et analytics
     */
    public function analytics(Request $request)
    {
        $period = $request->get('period', '30'); // 7, 30, 90 jours
        $startDate = now()->subDays((int)$period);

        // Évolution des tâches
        $task_evolution = Task::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN statut = "termine" THEN 1 ELSE 0 END) as completed')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Répartition par priorité
        $priority_distribution = Task::select('priorite', DB::raw('COUNT(*) as count'))
            ->whereIn('statut', ['a_faire', 'en_cours'])
            ->groupBy('priorite')
            ->get();

        // Performance par utilisateur
        $user_performance = User::withCount([
                'tasks as total_tasks',
                'tasks as completed_tasks' => function($query) {
                    $query->where('statut', 'termine');
                }
            ])
            ->where('role', 'technicien')
            ->active()
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'task_evolution' => $task_evolution,
                'priority_distribution' => $priority_distribution,
                'user_performance' => $user_performance
            ]
        ]);
    }
}
