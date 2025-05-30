<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Project;
use App\Models\User;
use App\Models\Task;
use App\Models\Event;
use Carbon\Carbon;

class ProjectController extends Controller
{
    /**
     * Afficher la liste des projets
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Project::with('responsable')->withCount(['tasks', 'completedTasks']);

        // Filtrage selon le rôle
        if ($user->role === 'technicien') {
            $query->where('id_responsable', $user->id)
                  ->orWhereHas('tasks', function($q) use ($user) {
                      $q->where('id_utilisateur', $user->id);
                  });
        }

        // Filtres de recherche
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('responsable') && $user->role === 'admin') {
            $query->where('id_responsable', $request->responsable);
        }

        if ($request->filled('zone')) {
            $query->where('zone_geographique', 'like', "%{$request->zone}%");
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('zone_geographique', 'like', "%{$search}%");
            });
        }

        // Tri
        $sortBy = $request->get('sort', 'date_creation');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $projects = $query->paginate(12);

        // Calcul du pourcentage d'avancement pour chaque projet
        $projects->getCollection()->transform(function ($project) {
            $project->pourcentage_avancement = $project->tasks_count > 0
                ? round(($project->completed_tasks_count / $project->tasks_count) * 100)
                : 0;
            return $project;
        });

        // Données pour les filtres
        $responsables = $user->role === 'admin' ? User::where('statut', 'actif')->get() : collect();

        return view('projects.index', compact('projects', 'responsables'));
    }

    /**
     * Afficher le formulaire de création d'un projet
     */
    public function create()
    {
        $this->authorize('create', Project::class);

        $users = User::where('statut', 'actif')->get();

        return view('projects.create', compact('users'));
    }

    /**
     * Enregistrer un nouveau projet
     */
    public function store(Request $request)
    {
        $this->authorize('create', Project::class);

        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:100|unique:projects,nom',
            'description' => 'required|string',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'zone_geographique' => 'required|string|max:100',
            'id_responsable' => 'required|exists:users,id',
        ], [
            'nom.required' => 'Le nom du projet est obligatoire.',
            'nom.unique' => 'Un projet avec ce nom existe déjà.',
            'description.required' => 'La description est obligatoire.',
            'date_debut.required' => 'La date de début est obligatoire.',
            'date_fin.required' => 'La date de fin est obligatoire.',
            'date_fin.after' => 'La date de fin doit être postérieure à la date de début.',
            'zone_geographique.required' => 'La zone géographique est obligatoire.',
            'id_responsable.required' => 'Vous devez désigner un responsable.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $project = Project::create([
            'nom' => $request->nom,
            'description' => $request->description,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'zone_geographique' => $request->zone_geographique,
            'id_responsable' => $request->id_responsable,
            'statut' => 'planifie',
        ]);

        // Log de l'action
        $this->logAction('CREATION', "Création du projet: {$project->nom}");

        return redirect()->route('projects.index')
                        ->with('success', 'Projet créé avec succès.');
    }

    /**
     * Afficher les détails d'un projet
     */
    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $project->load('responsable');

        // Statistiques du projet
        $stats = [
            'total_tasks' => $project->tasks()->count(),
            'completed_tasks' => $project->tasks()->where('statut', 'termine')->count(),
            'in_progress_tasks' => $project->tasks()->where('statut', 'en_cours')->count(),
            'pending_tasks' => $project->tasks()->where('statut', 'a_faire')->count(),
            'overdue_tasks' => $project->tasks()
                                     ->where('date_echeance', '<', Carbon::today())
                                     ->whereIn('statut', ['a_faire', 'en_cours'])
                                     ->count(),
            'total_events' => $project->events()->count(),
            'upcoming_events' => $project->events()
                                       ->where('date_debut', '>', Carbon::now())
                                       ->count(),
        ];

        // Pourcentage d'avancement
        $stats['progress_percentage'] = $stats['total_tasks'] > 0
            ? round(($stats['completed_tasks'] / $stats['total_tasks']) * 100)
            : 0;

        // Tâches récentes du projet
        $recent_tasks = $project->tasks()
                              ->with('user')
                              ->orderBy('date_creation', 'desc')
                              ->limit(10)
                              ->get();

        // Événements à venir
        $upcoming_events = $project->events()
                                 ->with('organisateur')
                                 ->where('date_debut', '>', Carbon::now())
                                 ->orderBy('date_debut')
                                 ->limit(5)
                                 ->get();

        // Activité récente (tâches et événements)
        $recent_activity = collect();

        // Tâches récemment modifiées
        $recent_task_updates = $project->tasks()
                                     ->where('date_modification', '>', Carbon::now()->subDays(7))
                                     ->with('user')
                                     ->orderBy('date_modification', 'desc')
                                     ->limit(5)
                                     ->get()
                                     ->map(function($task) {
                                         return [
                                             'type' => 'task',
                                             'title' => $task->titre,
                                             'description' => "Tâche mise à jour par {$task->user->nom}",
                                             'date' => $task->date_modification,
                                             'status' => $task->statut,
                                             'url' => route('tasks.show', $task->id)
                                         ];
                                     });

        $recent_activity = $recent_activity->concat($recent_task_updates);

        // Événements récents
        $recent_event_updates = $project->events()
                                      ->where('date_creation', '>', Carbon::now()->subDays(7))
                                      ->with('organisateur')
                                      ->orderBy('date_creation', 'desc')
                                      ->limit(3)
                                      ->get()
                                      ->map(function($event) {
                                          return [
                                              'type' => 'event',
                                              'title' => $event->titre,
                                              'description' => "Événement créé par {$event->organisateur->nom}",
                                              'date' => $event->date_creation,
                                              'status' => $event->statut,
                                              'url' => route('events.show', $event->id)
                                          ];
                                      });

        $recent_activity = $recent_activity->concat($recent_event_updates)
                                         ->sortByDesc('date')
                                         ->take(10);

        return view('projects.show', compact(
            'project',
            'stats',
            'recent_tasks',
            'upcoming_events',
            'recent_activity'
        ));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Project $project)
    {
        $this->authorize('update', $project);

        $users = User::where('statut', 'actif')->get();

        return view('projects.edit', compact('project', 'users'));
    }

    /**
     * Mettre à jour un projet
     */
    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:100|unique:projects,nom,' . $project->id,
            'description' => 'required|string',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'zone_geographique' => 'required|string|max:100',
            'id_responsable' => 'required|exists:users,id',
            'statut' => 'required|in:planifie,en_cours,termine,suspendu',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $oldStatus = $project->statut;

        $project->update([
            'nom' => $request->nom,
            'description' => $request->description,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'zone_geographique' => $request->zone_geographique,
            'id_responsable' => $request->id_responsable,
            'statut' => $request->statut,
        ]);

        // Log si changement de statut
        if ($oldStatus !== $request->statut) {
            $this->logAction('MODIFICATION', "Changement statut projet '{$project->nom}': {$oldStatus} → {$request->statut}");
        }

        return redirect()->route('projects.show', $project)
                        ->with('success', 'Projet mis à jour avec succès.');
    }

    /**
     * Supprimer un projet
     */
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        // Vérifier s'il y a des tâches ou événements associés
        if ($project->tasks()->count() > 0 || $project->events()->count() > 0) {
            return back()->with('error',
                'Impossible de supprimer ce projet car il contient des tâches ou des événements. Veuillez d\'abord les supprimer ou les réassigner.');
        }

        $projectName = $project->nom;
        $project->delete();

        $this->logAction('SUPPRESSION', "Suppression du projet: {$projectName}");

        return redirect()->route('projects.index')
                        ->with('success', 'Projet supprimé avec succès.');
    }

    /**
     * API pour obtenir la progression d'un projet
     */
    public function getProgress(Project $project)
    {
        $this->authorize('view', $project);

        $totalTasks = $project->tasks()->count();
        $completedTasks = $project->tasks()->where('statut', 'termine')->count();
        $inProgressTasks = $project->tasks()->where('statut', 'en_cours')->count();
        $pendingTasks = $project->tasks()->where('statut', 'a_faire')->count();

        $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        return response()->json([
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'in_progress_tasks' => $inProgressTasks,
            'pending_tasks' => $pendingTasks,
            'progress_percentage' => $progress,
            'status' => $project->statut,
        ]);
    }

    /**
     * API pour obtenir le calendrier des tâches d'un projet
     */
    public function getTasksCalendar(Project $project)
    {
        $this->authorize('view', $project);

        $tasks = $project->tasks()
                        ->with('user')
                        ->get()
                        ->map(function($task) {
                            $color = $this->getTaskColor($task->statut, $task->priorite);

                            return [
                                'id' => $task->id,
                                'title' => $task->titre,
                                'start' => $task->date_echeance->format('Y-m-d'),
                                'backgroundColor' => $color,
                                'borderColor' => $color,
                                'textColor' => '#ffffff',
                                'url' => route('tasks.show', $task->id),
                                'extendedProps' => [
                                    'assignee' => $task->user->nom . ' ' . $task->user->prenom,
                                    'status' => $task->statut,
                                    'priority' => $task->priorite,
                                    'progress' => $task->progression
                                ]
                            ];
                        });

        return response()->json($tasks);
    }

    /**
     * Rapport d'avancement d'un projet
     */
    public function generateReport(Project $project)
    {
        $this->authorize('view', $project);

        $project->load('responsable');

        // Statistiques détaillées
        $stats = [
            'project' => $project,
            'total_tasks' => $project->tasks()->count(),
            'completed_tasks' => $project->tasks()->where('statut', 'termine')->count(),
            'in_progress_tasks' => $project->tasks()->where('statut', 'en_cours')->count(),
            'pending_tasks' => $project->tasks()->where('statut', 'a_faire')->count(),
            'overdue_tasks' => $project->tasks()
                                     ->where('date_echeance', '<', Carbon::today())
                                     ->whereIn('statut', ['a_faire', 'en_cours'])
                                     ->count(),
            'total_events' => $project->events()->count(),
            'completed_events' => $project->events()->where('statut', 'termine')->count(),
        ];

        // Tâches par technicien
        $tasksByUser = $project->tasks()
                             ->with('user')
                             ->get()
                             ->groupBy('user.nom')
                             ->map(function($tasks, $userName) {
                                 return [
                                     'name' => $userName,
                                     'total' => $tasks->count(),
                                     'completed' => $tasks->where('statut', 'termine')->count(),
                                     'in_progress' => $tasks->where('statut', 'en_cours')->count(),
                                     'pending' => $tasks->where('statut', 'a_faire')->count(),
                                 ];
                             });

        // Tâches par priorité
        $tasksByPriority = $project->tasks()
                                 ->selectRaw('priorite, count(*) as count')
                                 ->groupBy('priorite')
                                 ->pluck('count', 'priorite')
                                 ->toArray();

        $this->logAction('EXPORT', "Génération du rapport pour le projet: {$project->nom}");

        return view('projects.report', compact(
            'project',
            'stats',
            'tasksByUser',
            'tasksByPriority'
        ));
    }

    /**
     * Couleur des tâches selon statut et priorité
     */
    private function getTaskColor($status, $priority)
    {
        if ($status === 'termine') {
            return '#28a745'; // Vert
        }

        switch ($priority) {
            case 'haute':
                return '#dc3545'; // Rouge
            case 'moyenne':
                return '#ffc107'; // Jaune
            case 'basse':
                return '#007bff'; // Bleu
            default:
                return '#6c757d'; // Gris
        }
    }

    /**
     * Log des actions
     */
    private function logAction($type, $description)
    {
        \App\Models\Journal::create([
            'date' => now(),
            'type_action' => $type,
            'description' => $description,
            'utilisateur_id' => Auth::id(),
            'adresse_ip' => request()->ip(),
        ]);
    }
}
