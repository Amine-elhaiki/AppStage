<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatedMiddleware
{
    /**
     * Handle an incoming request.
     * Middleware pour utilisateurs connectés (tous rôles confondus)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est connecté
        if (!Auth::check()) {
            return $this->handleUnauthenticated($request);
        }

        $user = Auth::user();

        // Vérifier si l'utilisateur est actif
        if ($user->statut !== 'actif') {
            return $this->handleInactiveUser($request, $user);
        }

        // Vérifier l'intégrité du compte utilisateur
        if (!$this->isValidUser($user)) {
            return $this->handleInvalidUser($request, $user);
        }

        // Mettre à jour la dernière activité (tous les 5 minutes pour éviter trop de requêtes)
        $this->updateUserActivity($user);

        // Ajouter des informations utilisateur à la requête
        $request->merge([
            'authenticated_user' => $user,
            'user_role' => $user->role,
            'is_admin' => $user->role === 'admin',
            'is_technician' => $user->role === 'technicien',
        ]);

        return $next($request);
    }

    /**
     * Gérer un utilisateur non connecté
     */
    private function handleUnauthenticated(Request $request): Response
    {
        // Sauvegarder l'URL demandée pour redirection après connexion
        $intendedUrl = $request->fullUrl();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentification requise.',
                'status_code' => 401,
                'errors' => ['Vous devez être connecté pour accéder à cette ressource.'],
                'login_url' => route('login')
            ], 401);
        }

        return redirect()->route('login')
            ->with('error', 'Vous devez être connecté pour accéder à cette page.')
            ->with('intended_url', $intendedUrl);
    }

    /**
     * Gérer un utilisateur inactif
     */
    private function handleInactiveUser(Request $request, $user): Response
    {
        // Logger la tentative d'accès avec compte inactif
        Log::warning('Tentative d\'accès avec compte inactif - ORMVAT', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'email' => $user->email,
            'statut' => $user->statut,
            'ip' => $request->ip(),
            'url' => $request->fullUrl(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now(),
        ]);

        // Déconnecter l'utilisateur
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Message personnalisé selon le statut
        $message = $this->getInactiveUserMessage($user->statut);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'status_code' => 403,
                'errors' => ['Compte utilisateur inactif'],
                'account_status' => $user->statut,
                'contact_admin' => true
            ], 403);
        }

        return redirect()->route('login')
            ->with('error', $message)
            ->with('account_status', $user->statut)
            ->with('show_contact_info', true);
    }

    /**
     * Gérer un utilisateur avec des données invalides
     */
    private function handleInvalidUser(Request $request, $user): Response
    {
        // Logger le problème de données utilisateur
        Log::error('Données utilisateur invalides détectées - ORMVAT', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'ip' => $request->ip(),
            'timestamp' => now(),
        ]);

        // Déconnecter l'utilisateur pour sécurité
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $message = 'Un problème a été détecté avec votre compte. Veuillez contacter l\'administrateur système.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'status_code' => 403,
                'errors' => ['Données de compte invalides'],
                'contact_admin' => true
            ], 403);
        }

        return redirect()->route('login')
            ->with('error', $message)
            ->with('system_error', true);
    }

    /**
     * Obtenir le message d'erreur selon le statut du compte
     */
    private function getInactiveUserMessage(string $statut): string
    {
        return match($statut) {
            'inactif' => 'Votre compte ORMVAT a été désactivé. Contactez votre responsable ou l\'administrateur système.',
            'suspendu' => 'Votre compte ORMVAT a été temporairement suspendu. Contactez l\'administrateur pour plus d\'informations.',
            'expire' => 'Votre compte ORMVAT a expiré. Veuillez contacter l\'administrateur pour le renouveler.',
            'bloque' => 'Votre compte ORMVAT a été bloqué pour des raisons de sécurité. Contactez immédiatement l\'administrateur.',
            'en_attente' => 'Votre compte ORMVAT est en attente de validation. Contactez l\'administrateur.',
            default => 'Votre compte ORMVAT n\'est pas actif. Contactez l\'administrateur système.'
        };
    }

    /**
     * Vérifier si l'utilisateur a des données valides
     */
    private function isValidUser($user): bool
    {
        // Vérifier les champs obligatoires
        $requiredFields = ['nom', 'prenom', 'email', 'role'];

        foreach ($requiredFields as $field) {
            if (empty($user->$field)) {
                return false;
            }
        }

        // Vérifier que le rôle est valide pour l'ORMVAT
        $validRoles = ['admin', 'technicien'];
        if (!in_array($user->role, $validRoles)) {
            return false;
        }

        // Vérifier que l'email est valide
        if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Vérifier que le compte n'a pas été créé avec des données suspectes
        if ($this->hasSuspiciousData($user)) {
            return false;
        }

        return true;
    }

    /**
     * Détecter des données suspectes dans le compte utilisateur
     */
    private function hasSuspiciousData($user): bool
    {
        // Vérifier si l'email contient des caractères suspects
        if (preg_match('/[<>"\']/', $user->email)) {
            return true;
        }

        // Vérifier si le nom/prénom contiennent des caractères suspects
        if (preg_match('/[<>"\'\{\}\[\]]/', $user->nom . $user->prenom)) {
            return true;
        }

        // Vérifier si le compte a été créé récemment mais avec une date de dernière connexion ancienne
        if ($user->created_at && $user->derniere_connexion) {
            $daysSinceCreation = $user->created_at->diffInDays(now());
            $daysSinceLastLogin = $user->derniere_connexion->diffInDays(now());

            // Si le compte est nouveau (moins de 7 jours) mais la dernière connexion date de plus de 30 jours
            if ($daysSinceCreation < 7 && $daysSinceLastLogin > 30) {
                return true;
            }
        }

        return false;
    }

    /**
     * Mettre à jour l'activité de l'utilisateur
     */
    private function updateUserActivity($user): void
    {
        // Mettre à jour seulement si la dernière mise à jour date de plus de 5 minutes
        $shouldUpdate = !$user->derniere_activite ||
                       $user->derniere_activite->diffInMinutes(now()) >= 5;

        if ($shouldUpdate) {
            try {
                $user->update([
                    'derniere_activite' => now(),
                    'ip_derniere_activite' => request()->ip()
                ]);
            } catch (\Exception $e) {
                // En cas d'erreur, logger mais ne pas bloquer la requête
                Log::warning('Erreur mise à jour activité utilisateur', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
