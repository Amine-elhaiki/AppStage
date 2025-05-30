<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Vérifier que l'utilisateur est connecté
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Vous devez être connecté pour accéder à cette page.');
        }

        $user = Auth::user();

        // Vérifier que l'utilisateur est actif
        if ($user->statut !== 'actif') {
            Auth::logout();
            return redirect('/login')->with('error', 'Votre compte a été désactivé.');
        }

        // Vérifier que l'utilisateur a l'un des rôles requis
        if (!in_array($user->role, $roles)) {
            $rolesString = implode(', ', $roles);
            abort(403, "Accès réservé aux utilisateurs avec le(s) rôle(s) : {$rolesString}");
        }

        return $next($request);
    }
}
