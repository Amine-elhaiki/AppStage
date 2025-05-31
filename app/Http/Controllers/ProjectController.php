<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Project::with(['responsable'])
            ->withCount(['tasks', 'tasks as completed_tasks_count' => function($q) {
                $q->where('statut', 'termine');
            }]);

        // Filtrage selon le rôle
        if (!Auth::user()->isAdmin()) {
            // Les techniciens voient seulement les projets où ils ont des tâches
            $query->whereHas('tasks', function($q) {
                $q->where('user_id', Auth::id());
            });
        }

        // Filtres
        if ($request->filled('status')) {
            $query->where('statut', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priorite', $request->priority);
        }

        if ($request->filled('responsable') && Auth::user()->isAdmin()) {
            $query->where('responsable_id', $request->responsable);
        }

        if ($request->filled('zone')) {
            $query->where('zone_geographique', 'like', '%' . $request->zone . '%');
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
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');

        if (in_array($sortBy, ['nom', 'statut', 'priorite', 'date_debut', 'date_fin', 'pourcentage_avancement'])) {
            $query->orderBy($sortBy, $sortDirection);
        }

        $projects = $query->paginate(12)->withQueryString();

        // Données pour les filtres
        $responsables = Auth::user()->isAdmin() ? User::orderBy('prenom')->get() : collect();
        $zones = Project::distinct()->pluck('zone_geographique')->filter()->sort();

        return view('projects.index', compact('projects', 'responsables', 'zones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Project::class);

        $users = User::active()->orderBy('prenom')->get();

        return view('projects.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Project::class);

        $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'required|string',
            'priorite' => 'required|in:basse,normale,haute,urgente',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'budget' => 'nullable|numeric|min:0',
            'zone_geographique' => 'required|string|max:255',
            'responsable_id' => 'required|exists:users,id',
        ], [
            'nom.required' => 'Le nom du projet est obligatoire.',
            'description.required' => 'La description est obligatoire.',
            'priorite.required' => 'La priorité est obligatoire.',
            'date_debut.required' => 'La date de début est obligatoire.',
            'date_fin.required' => 'La date de fin est obligatoire.',
            'date_fin.after' => 'La date de fin doit être postérieure à la date de début.',
            'budget.numeric' => 'Le budget doit être un nombre.',
            'budget.min' => 'Le budget ne peut pas être négatif.',
            'zone_geographique.required' => 'La zone géographique est obligatoire.',
            'responsable_id.required' => 'L\'assignation d\'un responsable est obligatoire.',
            'responsable_id.exists' => 'Le responsable sélectionné n\'existe pas.',
        ]);

        $project = Project::create([
            'nom' => $request->nom,
            'description' => $request->description,
            'statut' => 'planifie',
            'priorite' => $request->priorite,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'budget' => $request->budget,
            'zone_geographique' => $request->zone_geographique,
            'pourcentage_avancement' => 0,
            'responsable_id' => $request->responsable_id,
        ]);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Projet créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $project->load(['responsable', 'tasks.user', 'events.user', 'reports.user']);

        // Statistiques du projet
        $stats = [
            'total_tasks' => $project->tasks->count(),
            'completed_tasks' => $project->tasks->where('statut', 'termine')->count(),
            'in_progress_tasks' => $project->tasks->where('statut', 'en_cours')->count(),
            'overdue_tasks' => $project->tasks->filter(function($task) {
                return $task->isOverdue();
            })->count(),
            'total_events' => $project->events->count(),
            'completed_events' => $project->events->where('statut', 'termine')->count(),
            'total_reports' => $project->reports->count(),
            'validated_reports' => $project->reports->where('statut', 'valide')->count(),
            'budget_used' => $project->getBudgetUsed(),
            'budget_remaining' => $project->getBudgetRemaining(),
        ];

        // Tâches récentes
        $recentTasks = $project->tasks()
            ->with('user')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        // Événements à venir
        $upcomingEvents = $project->events()
            ->with('user')
            ->where('date_debut', '>', now())
            ->orderBy('date_debut')
            ->limit(3)
            ->get();

        // Membres de l'équipe
        $teamMembers = $project->getTeamMembers();

        return view('projects.show', compact('project', 'stats', 'recentTasks', 'upcomingEvents', 'teamMembers'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        $this->authorize('update', $project);

        $users = User::active()->orderBy('prenom')->get();

        return view('projects.edit', compact('project', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'required|string',
            'statut' => 'required|in:planifie,en_cours,suspendu,termine,annule',
            'priorite' => 'required|in:basse,normale,haute,urgente',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'budget' => 'nullable|numeric|min:0',
            'zone_geographique' => 'required|string|max:255',
            'pourcentage_avancement' => 'required|integer|min:0|max:100',
            'responsable_id' => 'required|exists:users,id',
        ]);

        $project->update($request->only([
            'nom', 'description', 'statut', 'priorite', 'date_debut', 'date_fin',
            'budget', 'zone_geographique', 'pourcentage_avancement', 'responsable_id'
        ]));

        return redirect()->route('projects.show', $project)
            ->with('success', 'Projet mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        // Vérifier s'il y a des tâches, événements ou rapports liés
        if ($project->tasks()->count() > 0 || $project->events()->count() > 0 || $project->reports()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer le projet car il contient des tâches, événements ou rapports.');
        }

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Projet supprimé avec succès.');
    }

    /**
     * Show project tasks
     */
    public function tasks(Project $project, Request $request)
    {
        $this->authorize('view', $project);

        $query = $project->tasks()->with(['user']);

        // Filtres
        if ($request->filled('status')) {
            $query->where('statut', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priorite', $request->priority);
        }

        if ($request->filled('user')) {
            $query->where('user_id', $request->user);
        }

        $tasks = $query->orderBy('date_echeance')->paginate(15);
        $users = User::active()->orderBy('prenom')->get();

        return view('projects.tasks', compact('project', 'tasks', 'users'));
    }

    /**
     * Add task to project
     */
    public function addTask(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'priorite' => 'required|in:basse,normale,haute,urgente',
            'date_echeance' => 'nullable|date|after:today',
            'user_id' => 'required|exists:users,id',
        ]);

        $task = $project->addTask([
            'titre' => $request->titre,
            'description' => $request->description,
            'priorite' => $request->priorite,
            'date_echeance' => $request->date_echeance,
            'user_id' => $request->user_id,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tâche ajoutée avec succès.',
            'task' => $task->load('user')
        ]);
    }

    /**
     * Update project status via AJAX
     */
    public function updateStatus(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $request->validate([
            'statut' => 'required|in:planifie,en_cours,suspendu,termine,annule',
        ]);

        $project->update(['statut' => $request->statut]);

        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour avec succès.',
            'project' => [
                'id' => $project->id,
                'statut' => $project->statut,
                'status_label' => $project->status_label,
                'status_color' => $project->status_color,
            ]
        ]);
    }

    /**
     * Get project progress
     */
    public function getProgress(Project $project)
    {
        $this->authorize('view', $project);

        $progress = [
            'pourcentage_avancement' => $project->pourcentage_avancement,
            'tasks_progress' => $project->progress_percentage,
            'total_tasks' => $project->tasks()->count(),
            'completed_tasks' => $project->tasks()->where('statut', 'termine')->count(),
            'budget_used' => $project->getBudgetUsed(),
            'budget_remaining' => $project->getBudgetRemaining(),
            'days_remaining' => $project->getRemainingDays(),
        ];

        return response()->json([
            'success' => true,
            'progress' => $progress
        ]);
    }

    /**
     * Update project progress
     */
    public function updateProgress(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $request->validate([
            'pourcentage_avancement' => 'required|integer|min:0|max:100',
        ]);

        $project->update(['pourcentage_avancement' => $request->pourcentage_avancement]);

        // Auto-completion si 100%
        if ($request->pourcentage_avancement >= 100 && $project->statut !== 'termine') {
            $project->update(['statut' => 'termine']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Progression mise à jour avec succès.',
            'project' => $project->fresh()
        ]);
    }

    /**
     * Get project statistics
     */
    public function stats()
    {
        $query = Project::query();

        // Filtrage selon le rôle
        if (!Auth::user()->isAdmin()) {
            $query->whereHas('tasks', function($q) {
                $q->where('user_id', Auth::id());
            });
        }

        $stats = [
            'total' => $query->count(),
            'active' => $query->clone()->whereIn('statut', ['planifie', 'en_cours'])->count(),
            'completed' => $query->clone()->where('statut', 'termine')->count(),
            'overdue' => $query->clone()->where('statut', '!=', 'termine')
                ->where('date_fin', '<', now())->count(),
            'by_status' => $query->clone()->selectRaw('statut, COUNT(*) as count')
                ->groupBy('statut')
                ->pluck('count', 'statut'),
            'by_priority' => $query->clone()->selectRaw('priorite, COUNT(*) as count')
                ->groupBy('priorite')
                ->pluck('count', 'priorite'),
            'budget_total' => $query->clone()->sum('budget'),
            'average_progress' => $query->clone()->avg('pourcentage_avancement'),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Export project data
     */
    public function export(Project $project)
    {
        $this->authorize('view', $project);

        $data = [
            'project' => $project->load(['responsable', 'tasks.user', 'events.user', 'reports.user']),
            'stats' => [
                'total_tasks' => $project->tasks->count(),
                'completed_tasks' => $project->tasks->where('statut', 'termine')->count(),
                'total_events' => $project->events->count(),
                'total_reports' => $project->reports->count(),
                'budget_used' => $project->getBudgetUsed(),
            ],
            'export_date' => now()->format('d/m/Y H:i'),
            'exported_by' => Auth::user()->prenom . ' ' . Auth::user()->nom,
        ];

        return view('projects.export', compact('data'));
    }

    /**
     * Duplicate project
     */
    public function duplicate(Project $project)
    {
        $this->authorize('create', Project::class);

        $newProject = $project->replicate();
        $newProject->nom = 'Copie de ' . $project->nom;
        $newProject->statut = 'planifie';
        $newProject->pourcentage_avancement = 0;
        $newProject->date_debut = now()->addWeek();
        $newProject->date_fin = now()->addWeek()->addDays($project->getDurationInDays() ?? 30);
        $newProject->save();

        return redirect()->route('projects.show', $newProject)
            ->with('success', 'Projet dupliqué avec succès.');
    }

    /**
     * Archive project
     */
    public function archive(Project $project)
    {
        $this->authorize('update', $project);

        $project->update(['statut' => 'termine']);

        return back()->with('success', 'Projet archivé avec succès.');
    }
}
