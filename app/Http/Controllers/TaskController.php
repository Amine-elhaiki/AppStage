<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Task::with(['user', 'project', 'creator']);

        // Filtrage selon le rôle
        if (!Auth::user()->isAdmin()) {
            $query->where('user_id', Auth::id());
        }

        // Filtres
        if ($request->filled('status')) {
            $query->where('statut', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priorite', $request->priority);
        }

        if ($request->filled('project')) {
            $query->where('project_id', $request->project);
        }

        if ($request->filled('user') && Auth::user()->isAdmin()) {
            $query->where('user_id', $request->user);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Tri
        $sortBy = $request->get('sort', 'date_echeance');
        $sortDirection = $request->get('direction', 'asc');

        if (in_array($sortBy, ['titre', 'statut', 'priorite', 'date_echeance', 'created_at'])) {
            $query->orderBy($sortBy, $sortDirection);
        }

        $tasks = $query->paginate(15)->withQueryString();

        // Données pour les filtres
        $projects = Project::orderBy('nom')->get();
        $users = Auth::user()->isAdmin() ? User::orderBy('prenom')->get() : collect();

        return view('tasks.index', compact('tasks', 'projects', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Task::class);

        $projects = Project::active()->orderBy('nom')->get();
        $users = User::active()->orderBy('prenom')->get();

        return view('tasks.create', compact('projects', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Task::class);

        $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'priorite' => 'required|in:basse,normale,haute,urgente',
            'date_echeance' => 'nullable|date|after:today',
            'user_id' => 'required|exists:users,id',
            'project_id' => 'nullable|exists:projects,id',
        ], [
            'titre.required' => 'Le titre est obligatoire.',
            'description.required' => 'La description est obligatoire.',
            'priorite.required' => 'La priorité est obligatoire.',
            'date_echeance.after' => 'La date d\'échéance doit être dans le futur.',
            'user_id.required' => 'L\'assignation à un utilisateur est obligatoire.',
            'user_id.exists' => 'L\'utilisateur sélectionné n\'existe pas.',
            'project_id.exists' => 'Le projet sélectionné n\'existe pas.',
        ]);

        $task = Task::create([
            'titre' => $request->titre,
            'description' => $request->description,
            'statut' => 'a_faire',
            'priorite' => $request->priorite,
            'date_creation' => now(),
            'date_echeance' => $request->date_echeance,
            'progression' => 0,
            'commentaires' => $request->commentaires,
            'user_id' => $request->user_id,
            'project_id' => $request->project_id,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Tâche créée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        $this->authorize('view', $task);

        $task->load(['user', 'project', 'creator']);

        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        $this->authorize('update', $task);

        $projects = Project::active()->orderBy('nom')->get();
        $users = User::active()->orderBy('prenom')->get();

        return view('tasks.edit', compact('task', 'projects', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'statut' => 'required|in:a_faire,en_cours,termine,reporte,annule',
            'priorite' => 'required|in:basse,normale,haute,urgente',
            'date_echeance' => 'nullable|date',
            'progression' => 'required|integer|min:0|max:100',
            'user_id' => 'required|exists:users,id',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        // Gestion automatique des dates selon le statut
        $data = $request->only(['titre', 'description', 'statut', 'priorite', 'date_echeance', 'progression', 'commentaires', 'user_id', 'project_id']);

        if ($request->statut === 'en_cours' && !$task->date_debut_reelle) {
            $data['date_debut_reelle'] = now();
        }

        if ($request->statut === 'termine') {
            $data['date_fin_reelle'] = now();
            $data['progression'] = 100;
        }

        $task->update($data);

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Tâche mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return redirect()->route('tasks.index')
            ->with('success', 'Tâche supprimée avec succès.');
    }

    /**
     * Update task status via AJAX
     */
    public function updateStatus(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $request->validate([
            'statut' => 'required|in:a_faire,en_cours,termine,reporte,annule',
        ]);

        $data = ['statut' => $request->statut];

        // Gestion automatique des dates et progression
        if ($request->statut === 'en_cours' && !$task->date_debut_reelle) {
            $data['date_debut_reelle'] = now();
        }

        if ($request->statut === 'termine') {
            $data['date_fin_reelle'] = now();
            $data['progression'] = 100;
        }

        $task->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour avec succès.',
            'task' => [
                'id' => $task->id,
                'statut' => $task->statut,
                'status_label' => $task->status_label,
                'progression' => $task->progression,
            ]
        ]);
    }

    /**
     * Quick update via AJAX (for checkboxes, etc.)
     */
    public function quickUpdate(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $request->validate([
            'statut' => 'sometimes|in:a_faire,en_cours,termine,reporte,annule',
            'progression' => 'sometimes|integer|min:0|max:100',
        ]);

        $data = $request->only(['statut', 'progression']);

        // Gestion automatique
        if (isset($data['statut'])) {
            if ($data['statut'] === 'en_cours' && !$task->date_debut_reelle) {
                $data['date_debut_reelle'] = now();
            }

            if ($data['statut'] === 'termine') {
                $data['date_fin_reelle'] = now();
                $data['progression'] = 100;
            }
        }

        if (isset($data['progression']) && $data['progression'] >= 100) {
            $data['statut'] = 'termine';
            $data['date_fin_reelle'] = now();
        }

        $task->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Tâche mise à jour avec succès.',
            'task' => $task->fresh()
        ]);
    }

    /**
     * Duplicate a task
     */
    public function duplicate(Task $task)
    {
        $this->authorize('create', Task::class);

        $newTask = $task->replicate();
        $newTask->titre = 'Copie de ' . $task->titre;
        $newTask->statut = 'a_faire';
        $newTask->progression = 0;
        $newTask->date_creation = now();
        $newTask->date_debut_reelle = null;
        $newTask->date_fin_reelle = null;
        $newTask->created_by = Auth::id();
        $newTask->save();

        return redirect()->route('tasks.show', $newTask)
            ->with('success', 'Tâche dupliquée avec succès.');
    }

    /**
     * Search tasks via AJAX
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $query = Task::with(['user', 'project'])
            ->where('titre', 'like', '%' . $request->q . '%')
            ->orWhere('description', 'like', '%' . $request->q . '%');

        // Filtrage selon le rôle
        if (!Auth::user()->isAdmin()) {
            $query->where('user_id', Auth::id());
        }

        $tasks = $query->limit(10)->get();

        return response()->json([
            'success' => true,
            'tasks' => $tasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'titre' => $task->titre,
                    'description' => Str::limit($task->description, 100),
                    'statut' => $task->statut,
                    'status_label' => $task->status_label,
                    'status_color' => $task->status_color,
                    'priorite' => $task->priorite,
                    'priority_label' => $task->priority_label,
                    'priority_color' => $task->priority_color,
                    'progression' => $task->progression,
                    'user' => $task->user ? [
                        'nom' => $task->user->nom,
                        'prenom' => $task->user->prenom,
                        'initials' => $task->user->initials,
                    ] : null,
                    'project' => $task->project ? [
                        'nom' => $task->project->nom,
                    ] : null,
                    'url' => route('tasks.show', $task),
                ];
            })
        ]);
    }

    /**
     * Get tasks statistics
     */
    public function stats()
    {
        $query = Task::query();

        // Filtrage selon le rôle
        if (!Auth::user()->isAdmin()) {
            $query->where('user_id', Auth::id());
        }

        $stats = [
            'total' => $query->count(),
            'a_faire' => $query->clone()->where('statut', 'a_faire')->count(),
            'en_cours' => $query->clone()->where('statut', 'en_cours')->count(),
            'termine' => $query->clone()->where('statut', 'termine')->count(),
            'en_retard' => $query->clone()->overdue()->count(),
            'due_today' => $query->clone()->dueToday()->count(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
}
