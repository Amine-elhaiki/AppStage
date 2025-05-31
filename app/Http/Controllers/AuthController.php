<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Afficher le formulaire de connexion
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Traiter la connexion
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'L\'adresse email est requise.',
            'email.email' => 'L\'adresse email doit être valide.',
            'password.required' => 'Le mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 6 caractères.',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        // Vérifier si l'utilisateur existe
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Ces identifiants ne correspondent à aucun compte.'],
            ]);
        }

        // Vérifier si l'utilisateur est actif
        if ($user->statut !== 'actif') {
            throw ValidationException::withMessages([
                'email' => ['Votre compte n\'est pas actif. Contactez l\'administrateur.'],
            ]);
        }

        // Tentative de connexion
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Mettre à jour la dernière connexion
            $user->updateLastLogin();

            // Redirection selon le rôle
            return $this->redirectToDashboard($user);
        }

        throw ValidationException::withMessages([
            'email' => ['Ces identifiants ne correspondent à aucun compte.'],
        ]);
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Vous avez été déconnecté avec succès.');
    }

    /**
     * Redirection vers le tableau de bord approprié
     */
    protected function redirectToDashboard(User $user)
    {
        $message = "Bienvenue {$user->prenom} ! Connexion réussie.";

        switch ($user->role) {
            case 'admin':
                return redirect()->route('dashboard.admin')->with('success', $message);
            case 'technicien':
                return redirect()->route('dashboard.technicien')->with('success', $message);
            case 'chef_equipe':
                return redirect()->route('dashboard.chef-equipe')->with('success', $message);
            default:
                return redirect()->route('dashboard')->with('success', $message);
        }
    }

    /**
     * API pour vérifier l'authentification
     */
    public function checkAuth(Request $request)
    {
        if (Auth::check()) {
            return response()->json([
                'authenticated' => true,
                'user' => [
                    'id' => Auth::id(),
                    'nom' => Auth::user()->nom,
                    'prenom' => Auth::user()->prenom,
                    'email' => Auth::user()->email,
                    'role' => Auth::user()->role,
                    'initials' => Auth::user()->initials,
                ]
            ]);
        }

        return response()->json(['authenticated' => false], 401);
    }

    /**
     * Changer le mot de passe
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Le mot de passe actuel est requis.',
            'new_password.required' => 'Le nouveau mot de passe est requis.',
            'new_password.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
            'new_password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $user = Auth::user();

        // Vérifier le mot de passe actuel
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Le mot de passe actuel est incorrect.'],
            ]);
        }

        // Mettre à jour le mot de passe
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mot de passe mis à jour avec succès.',
        ]);
    }

    /**
     * Profil utilisateur
     */
    public function profile()
    {
        $user = Auth::user();

        return view('auth.profile', compact('user'));
    }

    /**
     * Mettre à jour le profil
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'specialite' => 'nullable|string|max:255',
        ], [
            'nom.required' => 'Le nom est requis.',
            'prenom.required' => 'Le prénom est requis.',
        ]);

        $user->update($request->only(['nom', 'prenom', 'telephone', 'specialite']));

        return back()->with('success', 'Profil mis à jour avec succès.');
    }
}
