<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;

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

// Routes publiques (sans authentification)
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => config('app.version', '1.0.0')
    ]);
});

// Routes d'authentification
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'checkAuth']);
        Route::put('/password', [AuthController::class, 'changePassword']);
    });
});

// Routes protégées (nécessitent une authentification)
Route::middleware(['auth:sanctum'])->group(function () {

    // === DASHBOARD ===
    Route::prefix('dashboard')->group(function () {
        Route::get('/data', [DashboardController::class, 'apiData'])->name('api.dashboard.data');
        Route::get('/stats', [DashboardController::class, 'quickStats'])->name('api.dashboard.stats');
        Route::get('/analytics', [DashboardController::class, 'analytics'])->name('api.dashboard.analytics');
    });

    // === TÂCHES ===
    Route::prefix('tasks')->group(function () {
        // CRUD de base
        Route::get('/', [TaskController::class, 'index']);
        Route::post('/', [TaskController::class, 'store']);
        Route::get('/{task}', [TaskController::class, 'show']);
        Route::put('/{task}', [TaskController::class, 'update']);
        Route::delete('/{task}', [TaskController::class, 'destroy']);

        // Actions spécifiques
        Route::patch('/{task}/status', [TaskController::class, 'updateStatus']);
        Route::patch('/{task}/quick-update', [TaskController::class, 'quickUpdate']);
        Route::post('/{task}/duplicate', [TaskController::class, 'duplicate']);

        // Recherche et statistiques
        Route::get('/search/query', [TaskController::class, 'search']);
        Route::get('/statistics/overview', [TaskController::class, 'stats']);
    });

    // === PROJETS ===
    Route::prefix('projects')->group(function () {
        // CRUD de base
        Route::get('/', [ProjectController::class, 'index']);
        Route::post('/', [ProjectController::class, 'store']);
        Route::get('/{project}', [ProjectController::class, 'show']);
        Route::put('/{project}', [ProjectController::class, 'update']);
        Route::delete('/{project}', [ProjectController::class, 'destroy']);

        // Actions spécifiques
        Route::patch('/{project}/status', [ProjectController::class, 'updateStatus']);
        Route::get('/{project}/progress', [ProjectController::class, 'getProgress']);
        Route::patch('/{project}/progress', [ProjectController::class, 'updateProgress']);
        Route::post('/{project}/tasks', [ProjectController::class, 'addTask']);
        Route::post('/{project}/duplicate', [ProjectController::class, 'duplicate']);

        // Statistiques
        Route::get('/statistics/overview', [ProjectController::class, 'stats']);
    });

    // === ÉVÉNEMENTS ===
    Route::prefix('events')->group(function () {
        // CRUD de base
        Route::get('/', [EventController::class, 'index']);
        Route::post('/', [EventController::class, 'store']);
        Route::get('/{event}', [EventController::class, 'show']);
        Route::put('/{event}', [EventController::class, 'update']);
        Route::delete('/{event}', [EventController::class, 'destroy']);

        // Actions spécifiques
        Route::patch('/{event}/status', [EventController::class, 'updateStatus']);
        Route::get('/calendar/data', [EventController::class, 'calendarData']);
        Route::get('/upcoming/list', [EventController::class, 'upcoming']);

        // Statistiques
        Route::get('/statistics/overview', [EventController::class, 'stats']);
    });

    // === RAPPORTS ===
    Route::prefix('reports')->group(function () {
        // CRUD de base
        Route::get('/', [ReportController::class, 'index']);
        Route::post('/', [ReportController::class, 'store']);
        Route::get('/{report}', [
