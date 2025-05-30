<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier que l'utilisateur est connecté
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }
            return redirect('/login')->with('error', 'Vous devez être connecté pour accéder à cette page.');
        }

        // Récupérer l'utilisateur connecté
        $user = Auth::user();

        // Vérifier que l'utilisateur a le rôle admin
        if (!isset($user->role) || $user->role !== 'admin') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Accès interdit'], 403);
            }
            abort(403, 'Accès réservé aux administrateurs.');
        }

        // Vérifier que l'utilisateur est actif
        if (isset($user->statut) && $user->statut !== 'actif') {
            Auth::logout();
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Compte désactivé'], 403);
            }
            return redirect('/login')->with('error', 'Votre compte a été désactivé.');
        }

        return $next($request);
    }
}
