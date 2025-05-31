<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::withCount(['tasks', 'tasks as completed_tasks_count' => function($q) {
            $q->where('statut', 'termine');
        }, 'reports', 'events']);

        // Filtres
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('statut', $request->status);
        }

        if ($request->filled('specialite')) {
            $query->where('specialite', 'like', '%' . $request->specialite . '%');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('specialite', 'like', "%{$search}%");
            });
        }

        // Tri
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');

        if (in_array($sortBy, ['nom', 'prenom', 'email', 'role', 'statut', 'derniere_connexion', 'created_at'])) {
            $query->orderBy($sortBy, $sortDirection);
        }

        $users = $query->paginate(15)->withQueryString();

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', User::class);

        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,technicien,chef_equipe',
            'telephone' => 'nullable|string|max:20',
            'specialite' => 'nullable|string|max:255',
            'statut' => 'required|in:actif,inactif,suspendu',
        ], [
            'nom.required' => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'role.required' => 'Le rôle est obligatoire.',
            'statut.required' => 'Le statut est obligatoire.',
        ]);

        // Définir les permissions par défaut selon le rôle
        $permissions = match($request->role) {
            'admin' => ['all'],
            'chef_equipe' => ['manage_team', 'validate_reports', 'create_projects'],
            'technicien' => ['create_reports', 'manage_tasks'],
            default => []
        };

        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'telephone' => $request->telephone,
            'specialite' => $request->specialite,
            'statut' => $request->statut,
            'permissions' => $permissions,
        ]);

        return redirect()->route('users.show', $user)
            ->with('success', 'Utilisateur créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        $user->load(['tasks' => function($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }, 'reports' => function($query) {
            $query->orderBy('created_at', 'desc')->limit(5);
        }, 'events' => function($query) {
            $query->orderBy('date_debut', 'desc')->limit(5);
        }]);

        // Statistiques de l'utilisateur
        $stats = [
            'total_tasks' => $user->tasks()->count(),
            'completed_tasks' => $user->getCompletedTasksCount(),
            'overdue_tasks' => $user->getOverdueTasksCount(),
            'completion_rate' => $user->tasks()->count() > 0 ?
                round(($user->getCompletedTasksCount() / $user->tasks()->count()) * 100) : 0,
            'total_reports' => $user->reports()->count(),
            'validated_reports' => $user->reports()->where('statut', 'valide')->count(),
            'total_events' => $user->events()->count(),
            'completed_events' => $user->events()->where('statut', 'termine')->count(),
        ];

        return view('users.show', compact('user', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:admin,technicien,chef_equipe',
            'telephone' => 'nullable|string|max:20',
            'specialite' => 'nullable|string|max:255',
            'statut' => 'required|in:actif,inactif,suspendu',
        ]);

        // Mettre à jour les permissions si le rôle change
        $permissions = $user->permissions;
        if ($request->role !== $user->role) {
            $permissions = match($request->role) {
                'admin' => ['all'],
                'chef_equipe' => ['manage_team', 'validate_reports', 'create_projects'],
                'technicien' => ['create_reports', 'manage_tasks'],
                default => []
            };
        }

        $user->update([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'role' => $request->role,
            'telephone' => $request->telephone,
            'specialite' => $request->specialite,
            'statut' => $request->statut,
            'permissions' => $permissions,
        ]);

        return redirect()->route('users.show', $user)
            ->with('success', 'Utilisateur mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        // Vérifier que ce n'est pas le dernier admin
        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'Impossible de supprimer le dernier administrateur.');
        }

        // Vérifier qu'il n'y a pas de données liées
        if ($user->tasks()->count() > 0 || $user->reports()->count() > 0 || $user->events()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer cet utilisateur car il a des tâches, rapports ou événements associés.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }

    /**
     * Toggle user status
     */
    public function toggleStatus(User $user)
    {
        $this->authorize('update', $user);

        $newStatus = $user->statut === 'actif' ? 'inactif' : 'actif';
        $user->update(['statut' => $newStatus]);

        $message = $newStatus === 'actif' ? 'Utilisateur activé avec succès.' : 'Utilisateur désactivé avec succès.';

        return back()->with('success', $message);
    }

    /**
     * Reset user password
     */
    public function resetPassword(User $user)
    {
        $this->authorize('update', $user);

        $newPassword = Str::random(8);
        $user->update(['password' => Hash::make($newPassword)]);

        return back()->with('success', "Mot de passe réinitialisé. Nouveau mot de passe : {$newPassword}");
    }

    /**
     * Search users via AJAX
     */
    public function search(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $users = User::where('nom', 'like', '%' . $request->q . '%')
            ->orWhere('prenom', 'like', '%' . $request->q . '%')
            ->orWhere('email', 'like', '%' . $request->q . '%')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'users' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'email' => $user->email,
                    'role' => $user->role,
                    'statut' => $user->statut,
                    'initials' => $user->initials,
                    'full_name' => $user->full_name,
                    'url' => route('users.show', $user),
                ];
            })
        ]);
    }

    /**
     * Get user activity
     */
    public function activity(User $user)
    {
        $this->authorize('view', $user);

        $activities = [];

        // Tâches récentes
        $recentTasks = $user->tasks()
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        foreach ($recentTasks as $task) {
            $activities[] = [
                'type' => 'task',
                'action' => 'updated',
                'title' => $task->titre,
                'date' => $task->updated_at,
                'url' => route('tasks.show', $task),
                'status' => $task->statut,
            ];
        }

        // Rapports récents
        $recentReports = $user->reports()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        foreach ($recentReports as $report) {
            $activities[] = [
                'type' => 'report',
                'action' => 'created',
                'title' => $report->titre,
                'date' => $report->created_at,
                'url' => route('reports.show', $report),
                'status' => $report->statut,
            ];
        }

        // Événements récents
        $recentEvents = $user->events()
            ->orderBy('date_debut', 'desc')
            ->limit(10)
            ->get();

        foreach ($recentEvents as $event) {
            $activities[] = [
                'type' => 'event',
                'action' => 'scheduled',
                'title' => $event->titre,
                'date' => $event->date_debut,
                'url' => route('events.show', $event),
                'status' => $event->statut,
            ];
        }

        // Trier par date
        usort($activities, function($a, $b) {
            return $b['date'] <=> $a['date'];
        });

        return response()->json([
            'success' => true,
            'activities' => array_slice($activities, 0, 20) // Limiter à 20 activités
        ]);
    }

    /**
     * Get users statistics
     */
    public function stats()
    {
        $this->authorize('viewAny', User::class);

        $stats = [
            'total' => User::count(),
            'active' => User::where('statut', 'actif')->count(),
            'inactive' => User::where('statut', 'inactif')->count(),
            'suspended' => User::where('statut', 'suspendu')->count(),
            'by_role' => User::selectRaw('role, COUNT(*) as count')
                ->groupBy('role')
                ->pluck('count', 'role'),
            'recent_logins' => User::where('derniere_connexion', '>', now()->subDays(7))->count(),
            'never_logged' => User::whereNull('derniere_connexion')->count(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Bulk actions on users
     */
    public function bulkAction(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $request->validate([
            'action' => 'required|in:activate,deactivate,suspend,delete',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $users = User::whereIn('id', $request->user_ids)->get();
        $count = 0;

        foreach ($users as $user) {
            // Vérifications de sécurité
            if ($user->id === Auth::id()) {
                continue; // Ne pas modifier son propre compte
            }

            if ($request->action === 'delete' && $user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
                continue; // Ne pas supprimer le dernier admin
            }

            switch ($request->action) {
                case 'activate':
                    $user->update(['statut' => 'actif']);
                    $count++;
                    break;
                case 'deactivate':
                    $user->update(['statut' => 'inactif']);
                    $count++;
                    break;
                case 'suspend':
                    $user->update(['statut' => 'suspendu']);
                    $count++;
                    break;
                case 'delete':
                    if ($user->tasks()->count() === 0 && $user->reports()->count() === 0) {
                        $user->delete();
                        $count++;
                    }
                    break;
            }
        }

        $actionLabel = match($request->action) {
            'activate' => 'activés',
            'deactivate' => 'désactivés',
            'suspend' => 'suspendus',
            'delete' => 'supprimés',
        };

        return back()->with('success', "{$count} utilisateur(s) {$actionLabel} avec succès.");
    }

    /**
     * Export users list
     */
    public function export(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::withCount(['tasks', 'reports', 'events']);

        // Appliquer les mêmes filtres que l'index
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('statut', $request->status);
        }

        $users = $query->orderBy('nom')->get();

        return view('users.export', compact('users'));
    }
}
