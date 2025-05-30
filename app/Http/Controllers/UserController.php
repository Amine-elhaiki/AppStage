<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use App\Models\Report;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * Afficher la liste des utilisateurs (Admin seulement)
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::withCount(['assignedTasks', 'reports', 'managedProjects']);

        // Filtres de recherche
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        // Tri
        $sortBy = $request->get('sort', 'date_creation');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->paginate(15);

        return view('users.index', compact('users'));
    }

    /**
     * Afficher le formulaire de création d'utilisateur
     */
    public function create()
    {
        $this->authorize('create', User::class);

        return view('users.create');
    }

    /**
     * Enregistrer un nouvel utilisateur
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:50',
            'prenom' => 'required|string|max:50',
            'email' => 'required|email|max:100|unique:users,email',
            'telephone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,technicien',
            'statut' => 'required|in:actif,inactif',
            'password' => 'required|min:6|confirmed',
        ], [
            'nom.required' => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'email.required' => 'L\'email est obligatoire.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'role.required' => 'Le rôle est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 6 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'role' => $request->role,
            'statut' => $request->statut,
            'password' => Hash::make($request->password),
        ]);

        // Log de l'action
        $this->logAction('CREATION', "Création de l'utilisateur: {$user->nom} {$user->prenom} ({$user->email})");

        return redirect()->route('users.index')
                        ->with('success', 'Utilisateur créé avec succès.');
    }

    /**
     * Afficher les détails d'un utilisateur
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        // Statistiques de l'utilisateur
        $stats = [
            'total_tasks' => $user->assignedTasks()->count(),
            'completed_tasks' => $user->assignedTasks()->where('statut', 'termine')->count(),
            'in_progress_tasks' => $user->assignedTasks()->where('statut', 'en_cours')->count(),
            'pending_tasks' => $user->assignedTasks()->where('statut', 'a_faire')->count(),
            'overdue_tasks' => $user->assignedTasks()
                                   ->where('date_echeance', '<', Carbon::today())
                                   ->whereIn('statut', ['a_faire', 'en_cours'])
                                   ->count(),
            'total_reports' => $user->reports()->count(),
            'managed_projects' => $user->managedProjects()->count(),
            'active_projects' => $user->managedProjects()->where('statut', 'en_cours')->count(),
        ];

        // Tâches récentes
        $recent_tasks = $user->assignedTasks()
                           ->with('project')
                           ->orderBy('date_modification', 'desc')
                           ->limit(10)
                           ->get();

        // Rapports récents
        $recent_reports = $user->reports()
                             ->orderBy('date_creation', 'desc')
                             ->limit(5)
                             ->get();

        // Projets gérés
        $managed_projects = $user->managedProjects()
                                ->withCount(['tasks', 'completedTasks'])
                                ->orderBy('date_creation', 'desc')
                                ->get();

        // Activité récente (dernières connexions)
        $recent_activity = \App\Models\Journal::where('utilisateur_id', $user->id)
                                            ->orderBy('date', 'desc')
                                            ->limit(20)
                                            ->get();

        // Performance mensuelle (tâches terminées par mois)
        $monthly_performance = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $completed = $user->assignedTasks()
                             ->where('statut', 'termine')
                             ->whereYear('date_modification', $date->year)
                             ->whereMonth('date_modification', $date->month)
                             ->count();

            $monthly_performance->push([
                'month' => $date->format('M Y'),
                'completed_tasks' => $completed
            ]);
        }

        return view('users.show', compact(
            'user',
            'stats',
            'recent_tasks',
            'recent_reports',
            'managed_projects',
            'recent_activity',
            'monthly_performance'
        ));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return view('users.edit', compact('user'));
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:50',
            'prenom' => 'required|string|max:50',
            'email' => 'required|email|max:100|unique:users,email,' . $user->id,
            'telephone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,technicien',
            'statut' => 'required|in:actif,inactif',
            'password' => 'nullable|min:6|confirmed',
        ], [
            'nom.required' => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'email.required' => 'L\'email est obligatoire.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'role.required' => 'Le rôle est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 6 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $oldRole = $user->role;
        $oldStatus = $user->statut;

        $updateData = [
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'role' => $request->role,
            'statut' => $request->statut,
        ];

        // Mettre à jour le mot de passe si fourni
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        // Log des changements importants
        $changes = [];
        if ($oldRole !== $request->role) {
            $changes[] = "rôle: {$oldRole} → {$request->role}";
        }
        if ($oldStatus !== $request->statut) {
            $changes[] = "statut: {$oldStatus} → {$request->statut}";
        }
        if ($request->filled('password')) {
            $changes[] = "mot de passe modifié";
        }

        if (!empty($changes)) {
            $this->logAction('MODIFICATION', "Modification utilisateur {$user->nom} {$user->prenom}: " . implode(', ', $changes));
        }

        return redirect()->route('users.show', $user)
                        ->with('success', 'Utilisateur mis à jour avec succès.');
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        // Vérifier si l'utilisateur a des tâches ou projets assignés
        if ($user->assignedTasks()->count() > 0) {
            return back()->with('error',
                'Impossible de supprimer cet utilisateur car il a des tâches assignées. Veuillez d\'abord les réassigner.');
        }

        if ($user->managedProjects()->count() > 0) {
            return back()->with('error',
                'Impossible de supprimer cet utilisateur car il gère des projets. Veuillez d\'abord réassigner la responsabilité des projets.');
        }

        $userName = $user->nom . ' ' . $user->prenom;
        $user->delete();

        $this->logAction('SUPPRESSION', "Suppression de l'utilisateur: {$userName}");

        return redirect()->route('users.index')
                        ->with('success', 'Utilisateur supprimé avec succès.');
    }

    /**
     * Désactiver/Activer un utilisateur
     */
    public function toggleStatus(User $user)
    {
        $this->authorize('update', $user);

        $newStatus = $user->statut === 'actif' ? 'inactif' : 'actif';
        $user->statut = $newStatus;
        $user->save();

        $statusText = $newStatus === 'actif' ? 'activé' : 'désactivé';

        $this->logAction('MODIFICATION', "Utilisateur {$user->nom} {$user->prenom} {$statusText}");

        return response()->json([
            'success' => true,
            'message' => "Utilisateur {$statusText} avec succès",
            'status' => $newStatus
        ]);
    }

    /**
     * Réinitialiser le mot de passe d'un utilisateur
     */
    public function resetPassword(User $user)
    {
        $this->authorize('update', $user);

        // Générer un mot de passe temporaire
        $temporaryPassword = $this->generateTemporaryPassword();

        $user->password = Hash::make($temporaryPassword);
        $user->save();

        $this->logAction('MODIFICATION', "Réinitialisation du mot de passe pour: {$user->nom} {$user->prenom}");

        return response()->json([
            'success' => true,
            'message' => 'Mot de passe réinitialisé avec succès',
            'temporary_password' => $temporaryPassword
        ]);
    }

    /**
     * Statistiques des utilisateurs
     */
    public function statistics()
    {
        $this->authorize('viewAny', User::class);

        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('statut', 'actif')->count(),
            'inactive_users' => User::where('statut', 'inactif')->count(),
            'admins' => User::where('role', 'admin')->count(),
            'technicians' => User::where('role', 'technicien')->count(),
            'users_with_tasks' => User::whereHas('assignedTasks')->count(),
            'users_with_reports' => User::whereHas('reports')->count(),
        ];

        // Utilisateurs les plus actifs (par nombre de tâches terminées)
        $most_active_users = User::withCount([
                                    'assignedTasks as completed_tasks_count' => function($q) {
                                        $q->where('statut', 'termine');
                                    }
                                ])
                                ->where('statut', 'actif')
                                ->where('role', 'technicien')
                                ->orderBy('completed_tasks_count', 'desc')
                                ->limit(10)
                                ->get();

        // Utilisateurs par nombre de rapports
        $users_by_reports = User::withCount('reports')
                               ->where('statut', 'actif')
                               ->where('role', 'technicien')
                               ->orderBy('reports_count', 'desc')
                               ->limit(10)
                               ->get();

        // Nouvelles inscriptions par mois (12 derniers mois)
        $registrations_by_month = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = User::whereYear('date_creation', $date->year)
                        ->whereMonth('date_creation', $date->month)
                        ->count();

            $registrations_by_month->push([
                'month' => $date->format('M Y'),
                'count' => $count
            ]);
        }

        // Dernières connexions
        $recent_logins = \App\Models\Journal::where('type_action', 'CONNEXION')
                                          ->with('user')
                                          ->orderBy('date', 'desc')
                                          ->limit(20)
                                          ->get();

        return view('users.statistics', compact(
            'stats',
            'most_active_users',
            'users_by_reports',
            'registrations_by_month',
            'recent_logins'
        ));
    }

    /**
     * API pour obtenir la liste des utilisateurs actifs
     */
    public function getActiveUsers()
    {
        $users = User::where('statut', 'actif')
                    ->select('id', 'nom', 'prenom', 'email', 'role')
                    ->orderBy('nom')
                    ->get();

        return response()->json($users);
    }

    /**
     * API pour obtenir les statistiques d'un utilisateur
     */
    public function getUserStats(User $user)
    {
        $this->authorize('view', $user);

        $stats = [
            'user_id' => $user->id,
            'total_tasks' => $user->assignedTasks()->count(),
            'completed_tasks' => $user->assignedTasks()->where('statut', 'termine')->count(),
            'in_progress_tasks' => $user->assignedTasks()->where('statut', 'en_cours')->count(),
            'overdue_tasks' => $user->assignedTasks()
                                   ->where('date_echeance', '<', Carbon::today())
                                   ->whereIn('statut', ['a_faire', 'en_cours'])
                                   ->count(),
            'total_reports' => $user->reports()->count(),
            'reports_this_month' => $user->reports()
                                        ->whereMonth('date_creation', Carbon::now()->month)
                                        ->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Recherche d'utilisateurs
     */
    public function search(Request $request)
    {
        $query = User::query();

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $users = $query->orderBy('nom')
                      ->limit(20)
                      ->get();

        return response()->json($users);
    }

    /**
     * Générer un mot de passe temporaire
     */
    private function generateTemporaryPassword()
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';

        for ($i = 0; $i < 8; $i++) {
            $password .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $password;
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
