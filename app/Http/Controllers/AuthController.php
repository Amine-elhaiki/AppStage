<?php

// =============================================================================
// COPIEZ CES LIGNES EXACTEMENT EN HAUT DE VOTRE AUTHCONTROLLER.PHP
// =============================================================================

namespace App\Http\Controllers;

use App\Models\User;                      // ← LIGNE OBLIGATOIRE
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // =============================================================================
    // REMPLACEZ VOTRE MÉTHODE REGISTER PAR CELLE-CI :
    // =============================================================================

    public function register(Request $request)
    {
        // Validation simple
        $request->validate([
            'nom' => 'required|string|max:50',
            'prenom' => 'required|string|max:50',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role' => 'required|in:admin,technicien',
        ]);

        // SOLUTION A : Avec User::create() - RECOMMANDÉE
        try {
            $user = User::create([
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'statut' => 'actif',
                'telephone' => $request->telephone,
            ]);

            return redirect()->back()->with('success', 'Utilisateur créé avec succès !');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function registerAlternative(Request $request)
    {
        // Validation
        $request->validate([
            'nom' => 'required|string|max:50',
            'prenom' => 'required|string|max:50',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role' => 'required|in:admin,technicien',
        ]);

        // SOLUTION B : Avec new User() et save()
        try {
            // Étape 1 : Créer l'instance (vérifiez que User est importé en haut)
            $user = new User();

            // Étape 2 : Assigner les valeurs
            $user->nom = $request->nom;
            $user->prenom = $request->prenom;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->role = $request->role;
            $user->statut = 'actif';
            $user->telephone = $request->telephone;

            // Étape 3 : Sauvegarder
            $user->save();  // ← Cette ligne doit maintenant fonctionner

            return redirect()->back()->with('success', 'Utilisateur créé avec succès !');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    // =============================================================================
    // MÉTHODE DE TEST - AJOUTEZ CECI POUR TESTER
    // =============================================================================

    public function testSave()
    {
        try {
            // Test simple
            $user = new User();
            $user->nom = 'Test';
            $user->prenom = 'User';
            $user->email = 'test_' . time() . '@test.com';
            $user->password = Hash::make('password123');
            $user->role = 'technicien';
            $user->statut = 'actif';

            $result = $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Test save() réussi !',
                'user_id' => $user->id,
                'save_result' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ]);
        }
    }

    // =============================================================================
    // AUTRES MÉTHODES STANDARD (login, logout, etc.)
    // =============================================================================

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors(['email' => 'Identifiants incorrects.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function showLogin()
    {
        return view('auth.login');
    }
}

// =============================================================================
// VÉRIFIEZ AUSSI QUE VOTRE MODÈLE USER (app/Models/User.php) CONTIENT :
// =============================================================================

/*
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable  // ← LIGNE IMPORTANTE
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nom', 'prenom', 'email', 'password', 'role', 'statut', 'telephone'
    ];

    protected $hidden = ['password', 'remember_token'];
}
*/

// =============================================================================
// AJOUTEZ CETTE ROUTE DE TEST DANS routes/web.php :
// =============================================================================

/*
Route::get('/test-save', [App\Http\Controllers\AuthController::class, 'testSave']);
*/

// =============================================================================
// PUIS TESTEZ EN ALLANT SUR : http://votre-site.com/test-save
// =============================================================================
