<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Models\Event;
use Carbon\Carbon;

class TaskController extends Controller
{
    /**
     * Afficher la liste des tâches
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Task::with('user', 'project', 'event');

        // Filtrage selon le rôle
        if ($user->role === 'technicien') {
            $query->where('id_utilisateur', $user->id);
        }

        // Filtres de recherche
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('priorite')) {
            $query->where('priorite', $request->priorite);
        }

        if ($request->filled('utilisateur') && $user->role === 'admin') {
            $query->where('id_utilisateur', $request->utilisateur);
        }

        if ($request->filled('projet')) {
            $query->where('id_projet', $request->projet);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('date_echeance', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date_echeance', '<=', $request->date_fin);
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
        $sortOrder = $request->get('order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $tasks = $query->paginate(15);

        // Données pour les filtres
        $users = $user->role === 'admin' ? User::where('statut', 'actif')->get() : collect();
        $projects = Project::where('statut', '!=', 'termine')->get();

        return view('tasks.index', compact('tasks', 'users', 'projects'));
    }

    /**
     * Afficher le formulaire de création d'une tâche
     */
    public function create()
    {
        $this->authorize('create', Task::class);

        $users = User::where('statut', 'actif')->where('role', 'technicien')->get();
        $projects = Project::where('statut', '!=', 'termine')->get();
        $events = Event::where('statut', 'planifie')->get();

        return view('tasks.create', compact('users', 'projects', 'events'));
    }

    /**
     * Enregistrer une nouvelle tâche
     */
    public function store(Request $request)
    {
        $this->authorize('create', Task::class);

        $validator = Validator::make($request->all(), [
            'titre' => 'required|string|max:100',
            'description' => 'required|string',
            'date_echeance' => 'required|date|after_or_equal:today',
            'priorite' => 'required|in:basse,moyenne,haute',
            'id_utilisateur' => 'required|exists:users,id',
            'id_projet' => 'nullable|exists:projects,id',
            'id_evenement' => 'nullable|exists:events,id',
        ], [
            'titre.required' => 'Le titre est obligatoire.',
            'description.required' => 'La description est obligatoire.',
            'date_echeance.required' => 'La date d\'échéance est obligatoire.',
            'date_echeance.after_or_equal' => 'La date d\'échéance ne peut pas être antérieure à aujourd\'hui.',
            'id_utilisateur.required' => 'Vous devez assigner la tâche à un technicien.',
            'id_utilisateur.exists' => 'Le technicien sélectionné n\'existe pas.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $task = Task::create([
            'titre' => $request->titre,
            'description' => $request->description,
            'date_echeance' => $request->date_echeance,
            'priorite' => $request->priorite,
            'statut' => 'a_faire',
            'progression' => 0,
            'id_utilisateur' => $request->id_utilisateur,
            'id_projet' => $request->id_projet,
            'id_evenement' => $request->id_evenement,
        ]);

        // Log de l'action
        $this->logAction('CREATION', "Création de la tâche: {$task->titre}");

        return redirect()->route('tasks.index')
                        ->with('success', 'Tâche créée avec succès.');
    }

    /**
     * Afficher les détails d'une tâche
     */
    public function show(Task $task)
    {
        $this->authorize('view', $task);

        $task->load('user', 'project', 'event');

        return view('tasks.show', compact('task'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Task $task)
    {
        $this->authorize('update', $task);

        $users = User::where('statut', 'actif')->where('role', 'technicien')->get();
        $projects = Project::where('statut', '!=', 'termine')->get();
        $events = Event::where('statut', 'planifie')->get();

        return view('tasks.edit', compact('task', 'users', 'projects', 'events'));
    }

    /**
     * Mettre à jour une tâche
     */
    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validator = Validator::make($request->all(), [
            'titre' => 'required|string|max:100',
            'description' => 'required|string',
            'date_echeance' => 'required|date',
            'priorite' => 'required|in:basse,moyenne,haute',
            'statut' => 'required|in:a_faire,en_cours,termine',
            'progression' => 'required|integer|min:0|max:100',
            'id_utilisateur' => 'required|exists:users,id',
            'id_projet' => 'nullable|exists:projects,id',
            'id_evenement' => 'nullable|exists:events,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $oldStatus = $task->statut;

        $task->update([
            'titre' => $request->titre,
            'description' => $request->description,
            'date_echeance' => $request->date_echeance,
            'priorite' => $request->priorite,
            'statut' => $request->statut,
            'progression' => $request->progression,
            'id_utilisateur' => $request->id_utilisateur,
            'id_projet' => $request->id_projet,
            'id_evenement' => $request->id_evenement,
        ]);

        // Log si changement de statut
        if ($oldStatus !== $request->statut) {
            $this->logAction('MODIFICATION', "Changement statut tâche '{$task->titre}': {$oldStatus} → {$request->statut}");
        }

        return redirect()->route('tasks.index')
                        ->with('success', 'Tâche mise à jour avec succès.');
    }

    /**
     * Supprimer une tâche
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $taskTitle = $task->titre;
        $task->delete();

        $this->logAction('SUPPRESSION', "Suppression de la tâche: {$taskTitle}");

        return redirect()->route('tasks.index')
                        ->with('success', 'Tâche supprimée avec succès.');
    }

    /**
     * API pour mettre à jour le statut rapidement (AJAX)
     */
    public function updateStatus(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validator = Validator::make($request->all(), [
            'statut' => 'required|in:a_faire,en_cours,termine',
            'progression' => 'nullable|integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Données invalides'], 400);
        }

        $oldStatus = $task->statut;

        $task->statut = $request->statut;

        if ($request->filled('progression')) {
            $task->progression = $request->progression;
        } else {
            // Progression automatique selon le statut
            switch ($request->statut) {
                case 'a_faire':
                    $task->progression = 0;
                    break;
                case 'en_cours':
                    $task->progression = $task->progression ?: 25;
                    break;
                case 'termine':
                    $task->progression = 100;
                    break;
            }
        }

        $task->save();

        // Log si changement de statut
        if ($oldStatus !== $request->statut) {
            $this->logAction('MODIFICATION', "Changement statut tâche '{$task->titre}': {$oldStatus} → {$request->statut}");
        }

        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour avec succès',
            'task' => $task->load('user', 'project')
        ]);
    }

    /**
     * API pour obtenir les tâches en retard
     */
    public function getOverdueTasks()
    {
        $user = Auth::user();
        $query = Task::with('user', 'project')
                    ->where('date_echeance', '<', Carbon::today())
                    ->whereIn('statut', ['a_faire', 'en_cours']);

        if ($user->role === 'technicien') {
            $query->where('id_utilisateur', $user->id);
        }

        $overdueTasks = $query->orderBy('date_echeance')->get();

        return response()->json($overdueTasks);
    }

    /**
     * API pour obtenir les statistiques des tâches
     */
    public function getTaskStats()
    {
        $user = Auth::user();
        $query = Task::query();

        if ($user->role === 'technicien') {
            $query->where('id_utilisateur', $user->id);
        }

        $stats = [
            'total' => $query->count(),
            'a_faire' => $query->where('statut', 'a_faire')->count(),
            'en_cours' => $query->where('statut', 'en_cours')->count(),
            'termine' => $query->where('statut', 'termine')->count(),
            'en_retard' => $query->where('date_echeance', '<', Carbon::today())
                                ->whereIn('statut', ['a_faire', 'en_cours'])
                                ->count(),
            'cette_semaine' => $query->whereBetween('date_echeance', [
                                    Carbon::now()->startOfWeek(),
                                    Carbon::now()->endOfWeek()
                                ])->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Exporter les tâches en CSV
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $query = Task::with('user', 'project');

        if ($user->role === 'technicien') {
            $query->where('id_utilisateur', $user->id);
        }

        // Appliquer les mêmes filtres que dans index()
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $tasks = $query->orderBy('date_echeance')->get();

        $filename = 'taches_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($tasks) {
            $file = fopen('php://output', 'w');

            // En-têtes CSV
            fputcsv($file, [
                'ID', 'Titre', 'Description', 'Date échéance', 'Priorité',
                'Statut', 'Progression', 'Technicien', 'Projet', 'Date création'
            ]);

            // Données
            foreach ($tasks as $task) {
                fputcsv($file, [
                    $task->id,
                    $task->titre,
                    $task->description,
                    $task->date_echeance->format('d/m/Y'),
                    ucfirst($task->priorite),
                    ucfirst(str_replace('_', ' ', $task->statut)),
                    $task->progression . '%',
                    $task->user ? $task->user->nom . ' ' . $task->user->prenom : '',
                    $task->project ? $task->project->nom : '',
                    $task->date_creation->format('d/m/Y H:i')
                ]);
            }

            fclose($file);
        };

        $this->logAction('EXPORT', 'Export des tâches en CSV');

        return response()->stream($callback, 200, $headers);
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
