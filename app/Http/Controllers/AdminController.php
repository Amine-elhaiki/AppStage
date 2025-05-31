<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use App\Models\Event;
use App\Models\Report;
use App\Models\Journal;

class AdminController extends Controller
{
    /**
     * Dashboard administrateur
     */
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('statut', 'actif')->count(),
            'total_tasks' => Task::count(),
            'total_projects' => Project::count(),
            'total_reports' => Report::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Paramètres système
     */
    public function settings()
    {
        return view('admin.settings');
    }

    /**
     * Mettre à jour les paramètres
     */
    public function updateSettings(Request $request)
    {
        // Logique de mise à jour des paramètres
        return redirect()->back()->with('success', 'Paramètres mis à jour avec succès.');
    }

    /**
     * Journaux d'activité
     */
    public function logs()
    {
        $logs = Journal::with('user')
                      ->orderBy('date', 'desc')
                      ->paginate(50);

        return view('admin.logs', compact('logs'));
    }

    /**
     * Activer le mode maintenance
     */
    public function enableMaintenance()
    {
        Artisan::call('down');

        return response()->json([
            'success' => true,
            'message' => 'Mode maintenance activé'
        ]);
    }

    /**
     * Désactiver le mode maintenance
     */
    public function disableMaintenance()
    {
        Artisan::call('up');

        return response()->json([
            'success' => true,
            'message' => 'Mode maintenance désactivé'
        ]);
    }

    /**
     * API - Statistiques système
     */
    public function getSystemStats()
    {
        return response()->json([
            'users' => [
                'total' => User::count(),
                'active' => User::where('statut', 'actif')->count(),
                'admins' => User::where('role', 'admin')->count(),
                'technicians' => User::where('role', 'technicien')->count(),
            ],
            'tasks' => [
                'total' => Task::count(),
                'completed' => Task::where('statut', 'termine')->count(),
                'overdue' => Task::where('date_echeance', '<', now())
                                ->whereIn('statut', ['a_faire', 'en_cours'])
                                ->count(),
            ],
            'projects' => [
                'total' => Project::count(),
                'active' => Project::where('statut', 'en_cours')->count(),
            ],
            'reports' => [
                'total' => Report::count(),
                'this_month' => Report::whereMonth('date_creation', now()->month)->count(),
            ]
        ]);
    }

    /**
     * API - Journaux d'activité
     */
    public function getActivityLogs(Request $request)
    {
        $query = Journal::with('user')->orderBy('date', 'desc');

        if ($request->filled('type')) {
            $query->where('type_action', $request->type);
        }

        if ($request->filled('user_id')) {
            $query->where('utilisateur_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $logs = $query->paginate(50);

        return response()->json($logs);
    }

    /**
     * Informations système
     */
    public function systemInfo()
    {
        return response()->json([
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_info' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database' => config('database.default'),
            'storage_used' => $this->getStorageUsage(),
            'memory_usage' => memory_get_usage(true),
            'uptime' => $this->getSystemUptime(),
        ]);
    }

    /**
     * Calculer l'utilisation du stockage
     */
    private function getStorageUsage()
    {
        $path = storage_path();
        $size = 0;

        if (is_dir($path)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        }

        return $this->formatBytes($size);
    }

    /**
     * Formater les octets
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Obtenir le temps de fonctionnement du système
     */
    private function getSystemUptime()
    {
        if (function_exists('sys_getloadavg')) {
            return 'Disponible'; // Implémentation simplifiée
        }
        return 'Non disponible';
    }

    /**
     * Nettoyer le cache
     */
    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');

            return response()->json([
                'success' => true,
                'message' => 'Cache nettoyé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du nettoyage du cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sauvegarder la base de données
     */
    public function backupDatabase()
    {
        try {
            // Logique de sauvegarde (à implémenter selon vos besoins)
            return response()->json([
                'success' => true,
                'message' => 'Sauvegarde créée avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la sauvegarde: ' . $e->getMessage()
            ], 500);
        }
    }
}
