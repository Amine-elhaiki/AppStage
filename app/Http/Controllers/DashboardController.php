<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
use App\Models\Project;
use App\Models\Event;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Afficher le tableau de bord principal
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return $this->adminDashboard();
        }

        return $this->technicianDashboard();
    }

    /**
     * Tableau de bord administrateur
     */
    private function adminDashboard()
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        // Statistiques générales
        $stats = [
            'total_users' => User::where('statut', 'actif')->count(),
            'total_projects' => Project::count(),
            'active_projects' => Project::where('statut', 'en_cours')->count(),
            'total_tasks' => Task::count(),
            'pending_tasks' => Task::where('statut', 'a_faire')->count(),
            'in_progress_tasks' => Task::where('statut', 'en_cours')->count(),
            'completed_tasks' => Task::where('statut', 'termine')->count(),
            'overdue_tasks' => Task::where('date_echeance', '<', $today)
                                 ->whereIn('statut', ['a_faire', 'en_cours'])
                                 ->count(),
            'today_events' => Event::whereDate('date_debut', $today)->count(),
            'week_reports' => Report::where('date_creation', '>=', $thisWeek)->count(),
        ];

        // Tâches prioritaires
        $priority_tasks = Task::where('priorite', 'haute')
                             ->whereIn('statut', ['a_faire', 'en_cours'])
                             ->with('user', 'project')
                             ->orderBy('date_echeance')
                             ->limit(10)
                             ->get();

        // Événements d'aujourd'hui
        $today_events = Event::whereDate('date_debut', $today)
                            ->with('organisateur', 'participants')
                            ->orderBy('date_debut')
                            ->get();

        // Projets en cours avec progression
        $active_projects = Project::where('statut', 'en_cours')
                                 ->with('responsable')
                                 ->withCount(['tasks', 'completedTasks'])
                                 ->orderBy('date_fin')
                                 ->limit(5)
                                 ->get();

        // Rapports récents
        $recent_reports = Report::with('user')
                               ->orderBy('date_creation', 'desc')
                               ->limit(5)
                               ->get();

        // Utilisateurs actifs
        $active_users = User::where('statut', 'actif')
                           ->withCount(['assignedTasks' => function($q) {
                               $q->whereIn('statut', ['a_faire', 'en_cours']);
                           }])
                           ->orderBy('dernier_connexion', 'desc')
                           ->limit(10)
                           ->get();

        return view('dashboard.admin', compact(
            'stats',
            'priority_tasks',
            'today_events',
            'active_projects',
            'recent_reports',
            'active_users'
        ));
    }

    /**
     * Tableau de bord technicien
     */
    private function technicianDashboard()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();

        // Statistiques personnelles
        $stats = [
            'my_tasks' => Task::where('id_utilisateur', $user->id)->count(),
            'pending_tasks' => Task::where('id_utilisateur', $user->id)
                                  ->where('statut', 'a_faire')->count(),
            'in_progress_tasks' => Task::where('id_utilisateur', $user->id)
                                      ->where('statut', 'en_cours')->count(),
            'completed_tasks' => Task::where('id_utilisateur', $user->id)
                                    ->where('statut', 'termine')->count(),
            'overdue_tasks' => Task::where('id_utilisateur', $user->id)
                                  ->where('date_echeance', '<', $today)
                                  ->whereIn('statut', ['a_faire', 'en_cours'])
                                  ->count(),
            'my_events_today' => Event::whereDate('date_debut', $today)
                                     ->whereHas('participants', function($q) use ($user) {
                                         $q->where('id_utilisateur', $user->id);
                                     })->count(),
            'my_reports_week' => Report::where('id_utilisateur', $user->id)
                                      ->where('date_creation', '>=', $thisWeek)->count(),
        ];

        // Mes tâches d'aujourd'hui et prioritaires
        $my_priority_tasks = Task::where('id_utilisateur', $user->id)
                                ->where(function($q) use ($today) {
                                    $q->where('date_echeance', $today)
                                      ->orWhere('priorite', 'haute');
                                })
                                ->whereIn('statut', ['a_faire', 'en_cours'])
                                ->with('project')
                                ->orderBy('priorite', 'desc')
                                ->orderBy('date_echeance')
                                ->limit(8)
                                ->get();

        // Mes événements d'aujourd'hui
        $my_today_events = Event::whereDate('date_debut', $today)
                               ->whereHas('participants', function($q) use ($user) {
                                   $q->where('id_utilisateur', $user->id);
                               })
                               ->with('organisateur')
                               ->orderBy('date_debut')
                               ->get();

        // Mes projets actifs
        $my_projects = Project::where('id_responsable', $user->id)
                             ->orWhereHas('tasks', function($q) use ($user) {
                                 $q->where('id_utilisateur', $user->id);
                             })
                             ->where('statut', 'en_cours')
                             ->withCount(['tasks', 'completedTasks'])
                             ->limit(5)
                             ->get();

        // Mes rapports récents
        $my_recent_reports = Report::where('id_utilisateur', $user->id)
                                  ->orderBy('date_creation', 'desc')
                                  ->limit(5)
                                  ->get();

        return view('dashboard.technician', compact(
            'stats',
            'my_priority_tasks',
            'my_today_events',
            'my_projects',
            'my_recent_reports'
        ));
    }

    /**
     * API pour les données du tableau de bord (AJAX)
     */
    public function getDashboardData(Request $request)
    {
        $user = Auth::user();
        $type = $request->get('type', 'overview');

        switch ($type) {
            case 'tasks_chart':
                return $this->getTasksChartData($user);
            case 'projects_progress':
                return $this->getProjectsProgressData($user);
            case 'events_calendar':
                return $this->getEventsCalendarData($user);
            default:
                return response()->json(['error' => 'Type de données non reconnu'], 400);
        }
    }

    /**
     * Données pour le graphique des tâches
     */
    private function getTasksChartData($user)
    {
        $query = Task::query();

        if ($user->role === 'technicien') {
            $query->where('id_utilisateur', $user->id);
        }

        $tasks_by_status = $query->selectRaw('statut, count(*) as count')
                                ->groupBy('statut')
                                ->pluck('count', 'statut')
                                ->toArray();

        $tasks_by_priority = $query->selectRaw('priorite, count(*) as count')
                                  ->groupBy('priorite')
                                  ->pluck('count', 'priorite')
                                  ->toArray();

        return response()->json([
            'status' => $tasks_by_status,
            'priority' => $tasks_by_priority
        ]);
    }

    /**
     * Données de progression des projets
     */
    private function getProjectsProgressData($user)
    {
        $query = Project::query();

        if ($user->role === 'technicien') {
            $query->where('id_responsable', $user->id)
                  ->orWhereHas('tasks', function($q) use ($user) {
                      $q->where('id_utilisateur', $user->id);
                  });
        }

        $projects = $query->with('responsable')
                         ->withCount(['tasks', 'completedTasks'])
                         ->where('statut', 'en_cours')
                         ->get()
                         ->map(function($project) {
                             $progress = $project->tasks_count > 0
                                       ? round(($project->completed_tasks_count / $project->tasks_count) * 100)
                                       : 0;

                             return [
                                 'id' => $project->id,
                                 'nom' => $project->nom,
                                 'responsable' => $project->responsable->nom . ' ' . $project->responsable->prenom,
                                 'progress' => $progress,
                                 'tasks_count' => $project->tasks_count,
                                 'completed_tasks' => $project->completed_tasks_count
                             ];
                         });

        return response()->json($projects);
    }

    /**
     * Données pour le calendrier des événements
     */
    private function getEventsCalendarData($user)
    {
        $query = Event::query();

        if ($user->role === 'technicien') {
            $query->where('id_organisateur', $user->id)
                  ->orWhereHas('participants', function($q) use ($user) {
                      $q->where('id_utilisateur', $user->id);
                  });
        }

        $events = $query->whereBetween('date_debut', [
                        Carbon::now()->startOfMonth(),
                        Carbon::now()->endOfMonth()->addDays(7)
                    ])
                    ->with('organisateur')
                    ->get()
                    ->map(function($event) {
                        return [
                            'id' => $event->id,
                            'title' => $event->titre,
                            'start' => $event->date_debut->format('Y-m-d H:i:s'),
                            'end' => $event->date_fin->format('Y-m-d H:i:s'),
                            'backgroundColor' => $this->getEventColor($event->type),
                            'borderColor' => $this->getEventColor($event->type),
                            'textColor' => '#ffffff'
                        ];
                    });

        return response()->json($events);
    }

    /**
     * Couleur selon le type d'événement
     */
    private function getEventColor($type)
    {
        $colors = [
            'intervention' => '#dc3545',
            'reunion' => '#007bff',
            'formation' => '#28a745',
            'visite' => '#ffc107'
        ];

        return $colors[$type] ?? '#6c757d';
    }
}
