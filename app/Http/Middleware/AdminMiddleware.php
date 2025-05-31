<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Vous devez être connecté.');
        }

        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Accès refusé'], 403);
            }

            return redirect('/dashboard')->with('error', 'Accès réservé aux administrateurs.');
        }

        if ($user->statut !== 'actif') {
            Auth::logout();
            return redirect('/login')->with('error', 'Votre compte est désactivé.');
        }

        return $next($request);
    }
}
