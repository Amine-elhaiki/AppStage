<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Routes API authentifiées
Route::middleware('auth:sanctum')->group(function () {

    // Informations utilisateur authentifié
    Route::get('/user', function (Request $request) {
        return $request->user()->load('assignedTasks', 'managedProjects', 'organizedEvents');
    });

    // Dashboard API
    Route::prefix('dashboard')->group(function () {
        Route::get('/data', [DashboardController::class, 'getDashboardData']);
        Route::get('/stats', function (Request $request) {
            $user = $request->user();

            if ($user->role === 'admin') {
                return response()->json([
                    'total_users' => \App\Models\User::where('statut', 'actif')->count(),
                    'total_projects' => \App\Models\Project::count(),
                    'active_projects' => \App\Models\Project::where('statut', 'en_cours')->count(),
                    'total_tasks' => \App\Models\Task::count(),
                    'completed_tasks' => \App\Models\Task::where('statut', 'termine')->count(),
                    'overdue_tasks' => \App\Models\Task::where('date_echeance', '<', now())->whereIn('statut', ['a_faire', 'en_cours'])->count(),
                    'today_events' => \App\Models\Event::whereDate('date_debut', today())->count(),
                    'total_reports' => \App\Models\Report::count(),
                ]);
            }

            return response()->json([
                'my_tasks' => $user->assignedTasks()->count(),
                'my_pending_tasks' => $user->assignedTasks()->whereIn('statut', ['a_faire', 'en_cours'])->count(),
                'my_completed_tasks' => $user->assignedTasks()->where('statut', 'termine')->count(),
                'my_overdue_tasks' => $user->assignedTasks()->where('date_echeance', '<', now())->whereIn('statut', ['a_faire', 'en_cours'])->count(),
                'my_events_today' => \App\Models\Event::whereDate('date_debut', today())->whereHas('participants', function($q) use ($user) {
                    $q->where('id_utilisateur', $user->id);
                })->count(),
                'my_reports' => $user->reports()->count(),
            ]);
        });
    });

    // Tasks API
    Route::prefix('tasks')->group(function () {
        Route::get('/', function (Request $request) {
            $user = $request->user();
            $query = \App\Models\Task::with('user', 'project', 'event');

            if ($user->role === 'technicien') {
                $query->where('id_utilisateur', $user->id);
            }

            if ($request->has('status')) {
                $query->where('statut', $request->status);
            }

            if ($request->has('priority')) {
                $query->where('priorite', $request->priority);
            }

            if ($request->has('due_date')) {
                $query->whereDate('date_echeance', $request->due_date);
            }

            return $query->orderBy('date_echeance')->paginate(20);
        });

        Route::get('/overdue', [TaskController::class, 'getOverdueTasks']);
        Route::get('/stats', [TaskController::class, 'getTaskStats']);
        Route::patch('/{task}/status', [TaskController::class, 'updateStatus']);

        Route::get('/upcoming', function (Request $request) {
            $user = $request->user();
            $query = \App\Models\Task::with('user', 'project');

            if ($user->role === 'technicien') {
                $query->where('id_utilisateur', $user->id);
            }

            return $query->where('date_echeance', '>=', now())
                         ->where('date_echeance', '<=', now()->addDays(7))
                         ->whereIn('statut', ['a_faire', 'en_cours'])
                         ->orderBy('date_echeance')
                         ->get();
        });
    });

    // Projects API
    Route::prefix('projects')->group(function () {
        Route::get('/', function (Request $request) {
            $user = $request->user();
            $query = \App\Models\Project::with('responsable')->withCount(['tasks', 'completedTasks']);

            if ($user->role === 'technicien') {
                $query->where('id_responsable', $user->id)
                      ->orWhereHas('tasks', function($q) use ($user) {
                          $q->where('id_utilisateur', $user->id);
                      });
            }

            if ($request->has('status')) {
                $query->where('statut', $request->status);
            }

            return $query->orderBy('date_creation', 'desc')->get();
        });

        Route::get('/{project}/progress', [ProjectController::class, 'getProgress']);
        Route::get('/{project}/tasks-calendar', [ProjectController::class, 'getTasksCalendar']);

        Route::get('/active', function (Request $request) {
            $user = $request->user();
            $query = \App\Models\Project::where('statut', 'en_cours');

            if ($user->role === 'technicien') {
                $query->where('id_responsable', $user->id)
                      ->orWhereHas('tasks', function($q) use ($user) {
                          $q->where('id_utilisateur', $user->id);
                      });
            }

            return $query->select('id', 'nom', 'statut')->get();
        });
    });

    // Events API
    Route::prefix('events')->group(function () {
        Route::get('/calendar', [EventController::class, 'getCalendarEvents']);
        Route::get('/user', [EventController::class, 'getUserEvents']);
        Route::patch('/{event}/participation', [EventController::class, 'updateParticipation']);

        Route::get('/upcoming', function (Request $request) {
            $user = $request->user();

            return \App\Models\Event::with('organisateur', 'project')
                                   ->where(function($query) use ($user) {
                                       $query->where('id_organisateur', $user->id)
                                             ->orWhereHas('participants', function($q) use ($user) {
                                                 $q->where('id_utilisateur', $user->id);
                                             });
                                   })
                                   ->where('date_debut', '>', now())
                                   ->orderBy('date_debut')
                                   ->limit(10)
                                   ->get();
        });

        Route::get('/today', function (Request $request) {
            $user = $request->user();

            return \App\Models\Event::with('organisateur', 'participants')
                                   ->where(function($query) use ($user) {
                                       $query->where('id_organisateur', $user->id)
                                             ->orWhereHas('participants', function($q) use ($user) {
                                                 $q->where('id_utilisateur', $user->id);
                                             });
                                   })
                                   ->whereDate('date_debut', today())
                                   ->orderBy('date_debut')
                                   ->get();
        });
    });

    // Reports API
    Route::prefix('reports')->group(function () {
        Route::get('/recent', [ReportController::class, 'getRecentReports']);
        Route::get('/search', [ReportController::class, 'search']);

        Route::get('/stats', function (Request $request) {
            $user = $request->user();

            if ($user->role === 'admin') {
                return \App\Models\Report::getStatsForUser(null);
            }

            return \App\Models\Report::getStatsForUser($user->id);
        });

        Route::get('/types', function () {
            return \App\Models\Report::getMostCommonInterventionTypes();
        });
    });

    // Users API
    Route::prefix('users')->group(function () {
        Route::get('/active', [UserController::class, 'getActiveUsers']);
        Route::get('/search', [UserController::class, 'search']);
        Route::get('/{user}/stats', [UserController::class, 'getUserStats']);

        Route::get('/technicians', function () {
            return \App\Models\User::where('role', 'technicien')
                                  ->where('statut', 'actif')
                                  ->select('id', 'nom', 'prenom', 'email')
                                  ->orderBy('nom')
                                  ->get();
        });
    });

    // Notifications API
    Route::prefix('notifications')->group(function () {
        Route::get('/', function (Request $request) {
            $user = $request->user();

            return \App\Models\Notification::where('destinataire_id', $user->id)
                                          ->orderBy('date_creation', 'desc')
                                          ->limit(20)
                                          ->get();
        });

        Route::get('/unread-count', function (Request $request) {
            $user = $request->user();

            return response()->json([
                'count' => \App\Models\Notification::where('destinataire_id', $user->id)
                                                  ->where('lue', false)
                                                  ->count()
            ]);
        });

        Route::patch('/{notification}/read', function (Request $request, $id) {
            $notification = \App\Models\Notification::where('id', $id)
                                                   ->where('destinataire_id', $request->user()->id)
                                                   ->firstOrFail();

            $notification->markAsRead();

            return response()->json(['success' => true]);
        });

        Route::patch('/mark-all-read', function (Request $request) {
            \App\Models\Notification::where('destinataire_id', $request->user()->id)
                                   ->where('lue', false)
                                   ->update(['lue' => true]);

            return response()->json(['success' => true]);
        });
    });

    // Search global API
    Route::get('/search', function (Request $request) {
        $query = $request->get('q');
        $user = $request->user();

        if (empty($query)) {
            return response()->json([]);
        }

        $results = [];

        // Recherche dans les tâches
        $tasksQuery = \App\Models\Task::where('titre', 'like', "%{$query}%")
                                     ->orWhere('description', 'like', "%{$query}%");

        if ($user->role === 'technicien') {
            $tasksQuery->where('id_utilisateur', $user->id);
        }

        $tasks = $tasksQuery->with('user', 'project')->limit(5)->get();

        foreach ($tasks as $task) {
            $results[] = [
                'type' => 'task',
                'id' => $task->id,
                'title' => $task->titre,
                'description' => $task->description,
                'url' => route('tasks.show', $task->id),
                'meta' => $task->user->nom . ' - ' . $task->date_echeance->format('d/m/Y')
            ];
        }

        // Recherche dans les projets
        $projectsQuery = \App\Models\Project::where('nom', 'like', "%{$query}%")
                                           ->orWhere('description', 'like', "%{$query}%");

        if ($user->role === 'technicien') {
            $projectsQuery->where('id_responsable', $user->id)
                          ->orWhereHas('tasks', function($q) use ($user) {
                              $q->where('id_utilisateur', $user->id);
                          });
        }

        $projects = $projectsQuery->with('responsable')->limit(5)->get();

        foreach ($projects as $project) {
            $results[] = [
                'type' => 'project',
                'id' => $project->id,
                'title' => $project->nom,
                'description' => $project->description,
                'url' => route('projects.show', $project->id),
                'meta' => 'Responsable: ' . $project->responsable->nom
            ];
        }

        // Recherche dans les rapports
        $reportsQuery = \App\Models\Report::where('titre', 'like', "%{$query}%")
                                         ->orWhere('actions', 'like', "%{$query}%")
                                         ->orWhere('lieu', 'like', "%{$query}%");

        if ($user->role === 'technicien') {
            $reportsQuery->where('id_utilisateur', $user->id);
        }

        $reports = $reportsQuery->with('user')->limit(5)->get();

        foreach ($reports as $report) {
            $results[] = [
                'type' => 'report',
                'id' => $report->id,
                'title' => $report->titre,
                'description' => substr($report->actions, 0, 100) . '...',
                'url' => route('reports.show', $report->id),
                'meta' => $report->lieu . ' - ' . $report->date_intervention->format('d/m/Y')
            ];
        }

        return response()->json($results);
    });
});

// Route d'information sur l'API
Route::get('/', function () {
    return response()->json([
        'name' => 'PlanifTech API',
        'version' => '1.0.0',
        'description' => 'API pour la gestion des interventions techniques ORMVAT',
        'endpoints' => [
            'authentication' => 'Utilise Laravel Sanctum',
            'dashboard' => '/api/dashboard/*',
            'tasks' => '/api/tasks/*',
            'projects' => '/api/projects/*',
            'events' => '/api/events/*',
            'reports' => '/api/reports/*',
            'users' => '/api/users/*',
            'notifications' => '/api/notifications/*',
            'search' => '/api/search'
        ]
    ]);
});
