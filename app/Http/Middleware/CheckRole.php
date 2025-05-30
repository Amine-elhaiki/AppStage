<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Vérifier si l'utilisateur est connecté
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Vous devez être connecté pour accéder à cette page.');
        }

        $user = Auth::user();

        // Vérifier si l'utilisateur est actif
        if ($user->statut !== 'actif') {
            Auth::logout();
            return redirect('/login')->with('error', 'Votre compte a été désactivé. Contactez l\'administrateur.');
        }

        // Vérifier si l'utilisateur a le bon rôle
        if (!in_array($user->role, $roles)) {
            // Log de tentative d'accès non autorisé
            // Log::warning('Tentative d\'accès non autorisé', [
            //     'user_id' => $user->id,
            //     'user_role' => $user->role,
            //     'required_roles' => $roles,
            //     'url' => $request->url(),
            //     'ip' => $request->ip()
            // ]);

            // Rediriger avec message d'erreur selon le rôle
            if ($user->role === 'admin') {
                return redirect('/dashboard')->with('error', 'Accès non autorisé à cette fonctionnalité.');
            } else {
                return redirect('/dashboard')->with('error', 'Vous n\'avez pas les permissions nécessaires pour accéder à cette page.');
            }
        }

        return $next($request);
    }
}

// Middleware spécialisés pour plus de simplicité

class AdminMiddleware
{
    /**
     * Handle an incoming request - Admin uniquement
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Connexion requise.');
        }

        $user = Auth::user();

        if ($user->statut !== 'actif') {
            Auth::logout();
            return redirect('/login')->with('error', 'Compte désactivé.');
        }

        if ($user->role !== 'admin') {
            return redirect('/dashboard')->with('error', 'Accès réservé aux administrateurs.');
        }

        return $next($request);
    }
}

class TechnicianMiddleware
{
    /**
     * Handle an incoming request - Technicien ou Admin
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Connexion requise.');
        }

        $user = Auth::user();

        if ($user->statut !== 'actif') {
            Auth::logout();
            return redirect('/login')->with('error', 'Compte désactivé.');
        }

        if (!in_array($user->role, ['admin', 'technicien'])) {
            return redirect('/dashboard')->with('error', 'Accès non autorisé.');
        }

        return $next($request);
    }
}

class AuthenticatedMiddleware
{
    /**
     * Handle an incoming request - Utilisateur connecté uniquement
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Vous devez être connecté.');
        }

        $user = Auth::user();

        if ($user->statut !== 'actif') {
            Auth::logout();
            return redirect('/login')->with('error', 'Votre compte a été désactivé.');
        }

        return $next($request);
    }
}
