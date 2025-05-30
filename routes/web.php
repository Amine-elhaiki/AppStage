<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController; // ← CORRECTION : Import manquant
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - PlanifTech ORMVAT
|--------------------------------------------------------------------------
|
| Routes pour l'application de planification des interventions techniques
| de l'Office Régional de Mise en Valeur Agricole du Tadla (ORMVAT)
|
*/

// ========================================
// ROUTES PUBLIQUES (Non authentifiées)
// ========================================

Route::middleware('guest')->group(function () {
    // Redirection de la page d'accueil vers login
    Route::get('/', function () {
        return redirect('/login');
    })->name('home');

    // Authentification
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login'); // ← CORRECTION : showLogin au lieu de showLoginForm
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

    // Page d'information publique (optionnel)
    Route::get('/about', function () {
        return view('public.about');
    })->name('about');
});

// ========================================
// ROUTES AUTHENTIFIÉES (Tous utilisateurs connectés)
// ========================================

Route::middleware(['auth'])->group(function () {

    // ----------------------------------------
    // AUTHENTIFICATION ET PROFIL
    // ----------------------------------------

    // Déconnexion
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Profil utilisateur
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile.show');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    // ----------------------------------------
    // TABLEAU DE BORD
    // ----------------------------------------

    // Dashboard principal (tous les utilisateurs connectés)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // API pour données du tableau de bord
    Route::get('/api/dashboard-data', [DashboardController::class, 'getDashboardData'])->name('api.dashboard.data');

    // ----------------------------------------
    // API COMMUNES (Tous utilisateurs)
    // ----------------------------------------

    // API utilisateurs (pour sélections dans formulaires)
    Route::get('/api/users/active', [UserController::class, 'getActiveUsers'])->name('api.users.active');
    Route::get('/api/users/search', [UserController::class, 'search'])->name('api.users.search');
});

// ========================================
// ROUTES ADMIN + TECHNICIEN (Accès partagé)
// ========================================

Route::middleware(['auth', 'role:admin,technicien'])->group(function () {

    // ----------------------------------------
    // GESTION DES TÂCHES
    // ----------------------------------------

    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::get('/', [TaskController::class, 'index'])->name('index');
        Route::get('/create', [TaskController::class, 'create'])->name('create');
        Route::post('/', [TaskController::class, 'store'])->name('store');
        Route::get('/{task}', [TaskController::class, 'show'])->name('show');
        Route::get('/{task}/edit', [TaskController::class, 'edit'])->name('edit');
        Route::put('/{task}', [TaskController::class, 'update'])->name('update');
        Route::delete('/{task}', [TaskController::class, 'destroy'])->name('destroy');

        // Actions spéciales sur les tâches
        Route::patch('/{task}/status', [TaskController::class, 'updateStatus'])->name('updateStatus');
        Route::get('/export/excel', [TaskController::class, 'export'])->name('export');
    });

    // API Tâches
    Route::prefix('api/tasks')->name('api.tasks.')->group(function () {
        Route::get('/overdue', [TaskController::class, 'getOverdueTasks'])->name('overdue');
        Route::get('/stats', [TaskController::class, 'getTaskStats'])->name('stats');
        Route::get('/my-tasks', [TaskController::class, 'getMyTasks'])->name('my');
    });

    // ----------------------------------------
    // GESTION DES PROJETS
    // ----------------------------------------

    Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('index');
        Route::get('/create', [ProjectController::class, 'create'])->name('create');
        Route::post('/', [ProjectController::class, 'store'])->name('store');
        Route::get('/{project}', [ProjectController::class, 'show'])->name('show');
        Route::get('/{project}/edit', [ProjectController::class, 'edit'])->name('edit');
        Route::put('/{project}', [ProjectController::class, 'update'])->name('update');
        Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('destroy');

        // Actions spéciales sur les projets
        Route::get('/{project}/progress', [ProjectController::class, 'getProgress'])->name('progress');
        Route::get('/{project}/tasks-calendar', [ProjectController::class, 'getTasksCalendar'])->name('tasks-calendar');
        Route::get('/{project}/report', [ProjectController::class, 'generateReport'])->name('report');
    });

    // ----------------------------------------
    // GESTION DES ÉVÉNEMENTS
    // ----------------------------------------

    Route::prefix('events')->name('events.')->group(function () {
        Route::get('/', [EventController::class, 'index'])->name('index');
        Route::get('/create', [EventController::class, 'create'])->name('create');
        Route::post('/', [EventController::class, 'store'])->name('store');
        Route::get('/{event}', [EventController::class, 'show'])->name('show');
        Route::get('/{event}/edit', [EventController::class, 'edit'])->name('edit');
        Route::put('/{event}', [EventController::class, 'update'])->name('update');
        Route::delete('/{event}', [EventController::class, 'destroy'])->name('destroy');

        // Actions spéciales sur les événements
        Route::patch('/{event}/participation', [EventController::class, 'updateParticipation'])->name('updateParticipation');
        Route::post('/{event}/attendance', [EventController::class, 'markAttendance'])->name('markAttendance');
    });

    // Calendrier des événements
    Route::get('/calendar', [EventController::class, 'calendar'])->name('events.calendar');

    // API Événements
    Route::prefix('api/events')->name('api.events.')->group(function () {
        Route::get('/calendar', [EventController::class, 'getCalendarEvents'])->name('calendar');
        Route::get('/user', [EventController::class, 'getUserEvents'])->name('user');
        Route::get('/upcoming', [EventController::class, 'getUpcomingEvents'])->name('upcoming');
    });

    // ----------------------------------------
    // GESTION DES RAPPORTS
    // ----------------------------------------

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/create', [ReportController::class, 'create'])->name('create');
        Route::post('/', [ReportController::class, 'store'])->name('store');
        Route::get('/{report}', [ReportController::class, 'show'])->name('show');
        Route::get('/{report}/edit', [ReportController::class, 'edit'])->name('edit');
        Route::put('/{report}', [ReportController::class, 'update'])->name('update');
        Route::delete('/{report}', [ReportController::class, 'destroy'])->name('destroy');

        // Export et téléchargements
        Route::get('/{report}/export-html', [ReportController::class, 'exportHTML'])->name('export.html');
        Route::get('/{report}/export-text', [ReportController::class, 'exportText'])->name('export.text');
        Route::get('/{report}/print', [ReportController::class, 'printView'])->name('print');

        // Gestion des pièces jointes
        Route::get('/attachments/{attachment}/download', [ReportController::class, 'downloadAttachment'])->name('attachment.download');
        Route::delete('/attachments/{attachment}', [ReportController::class, 'deleteAttachment'])->name('attachment.delete');

        // Statistiques des rapports
        Route::get('/statistics/dashboard', [ReportController::class, 'statistics'])->name('statistics');
    });

    // API Rapports
    Route::prefix('api/reports')->name('api.reports.')->group(function () {
        Route::get('/recent', [ReportController::class, 'getRecentReports'])->name('recent');
        Route::get('/search', [ReportController::class, 'search'])->name('search');
        Route::get('/stats', [ReportController::class, 'getReportStats'])->name('stats');
    });
});

// ========================================
// ROUTES ADMIN UNIQUEMENT
// ========================================

Route::middleware(['auth', 'admin'])->group(function () {

    // ----------------------------------------
    // DASHBOARD ADMINISTRATEUR
    // ----------------------------------------

    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');

    // ----------------------------------------
    // GESTION DES UTILISATEURS
    // ----------------------------------------

    Route::prefix('admin/users')->name('admin.users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');

        // Actions spéciales utilisateurs
        Route::patch('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggleStatus');
        Route::post('/{user}/reset-password', [UserController::class, 'resetPassword'])->name('resetPassword');

        // Statistiques utilisateurs
        Route::get('/statistics/overview', [UserController::class, 'statistics'])->name('statistics');
    });

    // ----------------------------------------
    // CRÉATION D'UTILISATEURS
    // ----------------------------------------

    Route::get('/admin/register', [AuthController::class, 'showRegister'])->name('admin.register');
    Route::post('/admin/register', [AuthController::class, 'register'])->name('admin.register.submit');

    // ----------------------------------------
    // API ADMIN
    // ----------------------------------------

    Route::prefix('api/admin')->name('api.admin.')->group(function () {
        Route::get('/users/{user}/stats', [UserController::class, 'getUserStats'])->name('users.stats');
        Route::get('/system/stats', [AdminController::class, 'getSystemStats'])->name('system.stats');
        Route::get('/activity/logs', [AdminController::class, 'getActivityLogs'])->name('activity.logs');
    });

    // ----------------------------------------
    // CONFIGURATION SYSTÈME
    // ----------------------------------------

    Route::prefix('admin/system')->name('admin.system.')->group(function () {
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::put('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
        Route::get('/logs', [AdminController::class, 'logs'])->name('logs');
        Route::post('/maintenance/enable', [AdminController::class, 'enableMaintenance'])->name('maintenance.enable');
        Route::post('/maintenance/disable', [AdminController::class, 'disableMaintenance'])->name('maintenance.disable');
    });
});

// ========================================
// ROUTES TECHNICIEN UNIQUEMENT
// ========================================

Route::middleware(['auth', 'technicien'])->group(function () {

    // ----------------------------------------
    // DASHBOARD TECHNICIEN
    // ----------------------------------------

    Route::get('/technicien/dashboard', [DashboardController::class, 'technicien'])->name('technicien.dashboard');

    // ----------------------------------------
    // MES ACTIVITÉS (Technicien)
    // ----------------------------------------

    Route::prefix('technicien')->name('technicien.')->group(function () {
        // Mes tâches personnelles
        Route::get('/my-tasks', [TaskController::class, 'myTasks'])->name('tasks.my');
        Route::get('/my-reports', [ReportController::class, 'myReports'])->name('reports.my');
        Route::get('/my-events', [EventController::class, 'myEvents'])->name('events.my');

        // Planning personnel
        Route::get('/planning', [EventController::class, 'myPlanning'])->name('planning');
        Route::get('/calendar', [EventController::class, 'myCalendar'])->name('calendar');
    });
});

// ========================================
// NOTIFICATIONS (Tous utilisateurs connectés)
// ========================================

Route::middleware(['auth'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', function () {
        $notifications = auth()->user()->notifications ?? [];
        return view('notifications.index', compact('notifications'));
    })->name('index');

    Route::patch('/{notification}/read', function ($id) {
        try {
            $notification = \App\Models\Notification::findOrFail($id);
            $notification->markAsRead();
            return response()->json(['success' => true, 'message' => 'Notification marquée comme lue']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Notification non trouvée'], 404);
        }
    })->name('markAsRead');

    Route::post('/mark-all-read', function () {
        try {
            auth()->user()->unreadNotifications->markAsRead();
            return response()->json(['success' => true, 'message' => 'Toutes les notifications marquées comme lues']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur lors de la mise à jour'], 500);
        }
    })->name('markAllAsRead');
});

// ========================================
// ROUTES DE DÉVELOPPEMENT (Local uniquement)
// ========================================

if (app()->environment('local', 'testing')) {
    Route::prefix('dev')->name('dev.')->group(function () {
        // Test dashboard
        Route::get('/test-dashboard', function () {
            return view('dashboard.test');
        })->name('test.dashboard');

        // Test des middlewares
        Route::get('/test-admin', function () {
            return response()->json([
                'message' => 'Accès admin autorisé !',
                'user' => auth()->user()->nom ?? 'Anonyme',
                'role' => auth()->user()->role ?? 'Non défini'
            ]);
        })->middleware(['auth', 'admin'])->name('test.admin');

        Route::get('/test-technicien', function () {
            return response()->json([
                'message' => 'Accès technicien autorisé !',
                'user' => auth()->user()->nom ?? 'Anonyme',
                'role' => auth()->user()->role ?? 'Non défini'
            ]);
        })->middleware(['auth', 'technicien'])->name('test.technicien');

        // Test des permissions multi-rôles
        Route::get('/test-multi-role', function () {
            return response()->json([
                'message' => 'Accès multi-rôles autorisé !',
                'user' => auth()->user()->nom ?? 'Anonyme',
                'role' => auth()->user()->role ?? 'Non défini'
            ]);
        })->middleware(['auth', 'role:admin,technicien'])->name('test.multi.role');

        // Informations de debug
        Route::get('/info', function () {
            return response()->json([
                'environment' => app()->environment(),
                'user' => auth()->user() ?? 'Non connecté',
                'middlewares_loaded' => array_keys(app('router')->getMiddleware()),
                'routes_count' => count(app('router')->getRoutes()),
            ]);
        })->name('info');
    });
}

// ========================================
// ROUTES D'ERREUR ET FALLBACK
// ========================================

// Page d'erreur 403 personnalisée
Route::get('/unauthorized', function () {
    return view('errors.403', [
        'message' => 'Vous n\'avez pas les permissions nécessaires pour accéder à cette page.'
    ]);
})->name('unauthorized');

// Route de fallback pour les pages inexistantes
Route::fallback(function () {
    return response()->view('errors.404', [
        'message' => 'La page que vous recherchez n\'existe pas.',
        'suggestion' => 'Retournez au tableau de bord principal.'
    ], 404);
});

// ========================================
// ROUTES DE SANTÉ ET MONITORING
// ========================================

// Health check pour monitoring (sans authentification)
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => config('app.version', '1.0.0'),
        'environment' => app()->environment(),
    ]);
})->name('health.check');

// Status de l'application (nécessite authentification admin)
Route::middleware(['auth', 'admin'])->get('/admin/status', function () {
    return response()->json([
        'database' => 'connected', // Vous pouvez ajouter une vraie vérification DB
        'storage' => is_writable(storage_path()) ? 'writable' : 'read-only',
        'cache' => 'active', // Vous pouvez ajouter une vraie vérification cache
        'users_count' => \App\Models\User::count(),
        'tasks_count' => \App\Models\Task::count() ?? 0,
        'projects_count' => \App\Models\Project::count() ?? 0,
        'reports_count' => \App\Models\Report::count() ?? 0,
    ]);
})->name('admin.status');
