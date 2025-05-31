<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Event;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Report::with(['user', 'event', 'project']);

        // Filtrage selon le rôle
        if (!Auth::user()->isAdmin() && !Auth::user()->isChefEquipe()) {
            $query->where('user_id', Auth::id());
        }

        // Filtres
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('statut', $request->status);
        }

        if ($request->filled('project')) {
            $query->where('project_id', $request->project);
        }

        if ($request->filled('user') && (Auth::user()->isAdmin() || Auth::user()->isChefEquipe())) {
            $query->where('user_id', $request->user);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date_intervention', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date_intervention', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('lieu', 'like', "%{$search}%")
                  ->orWhere('probleme_identifie', 'like', "%{$search}%");
            });
        }

        // Tri
        $sortBy = $request->get('sort', 'date_intervention');
        $sortDirection = $request->get('direction', 'desc');

        if (in_array($sortBy, ['titre', 'type', 'statut', 'date_intervention', 'lieu', 'cout_intervention'])) {
            $query->orderBy($sortBy, $sortDirection);
        }

        $reports = $query->paginate(15)->withQueryString();

        // Données pour les filtres
        $projects = Project::orderBy('nom')->get();
        $users = (Auth::user()->isAdmin() || Auth::user()->isChefEquipe()) ? User::orderBy('prenom')->get() : collect();

        return view('reports.index', compact('reports', 'projects', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $projects = Project::active()->orderBy('nom')->get();
        $events = Event::where('user_id', Auth::id())
            ->where('statut', '!=', 'annule')
            ->orderBy('date_debut', 'desc')
            ->get();

        // Pré-remplir avec un événement si spécifié
        $selectedEvent = null;
        if ($request->filled('event_id')) {
            $selectedEvent = Event::find($request->event_id);
        }

        return view('reports.create', compact('projects', 'events', 'selectedEvent'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:intervention,maintenance,inspection,reparation,installation,autre',
            'date_intervention' => 'required|date|before_or_equal:today',
            'lieu' => 'required|string|max:255',
            'probleme_identifie' => 'nullable|string',
            'actions_effectuees' => 'required|string',
            'materiels_utilises' => 'nullable|string',
            'etat_equipement' => 'nullable|in:bon,moyen,mauvais,hors_service',
            'recommandations' => 'nullable|string',
            'cout_intervention' => 'nullable|numeric|min:0',
            'event_id' => 'nullable|exists:events,id',
            'project_id' => 'nullable|exists:projects,id',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max par photo
        ], [
            'titre.required' => 'Le titre est obligatoire.',
            'description.required' => 'La description est obligatoire.',
            'type.required' => 'Le type de rapport est obligatoire.',
            'date_intervention.required' => 'La date d\'intervention est obligatoire.',
            'date_intervention.before_or_equal' => 'La date d\'intervention ne peut pas être dans le futur.',
            'lieu.required' => 'Le lieu est obligatoire.',
            'actions_effectuees.required' => 'Les actions effectuées sont obligatoires.',
            'cout_intervention.numeric' => 'Le coût doit être un nombre.',
            'cout_intervention.min' => 'Le coût ne peut pas être négatif.',
            'photos.*.image' => 'Le fichier doit être une image.',
            'photos.*.mimes' => 'L\'image doit être au format JPEG, PNG, JPG ou GIF.',
            'photos.*.max' => 'L\'image ne doit pas dépasser 5MB.',
        ]);

        // Gestion des photos
        $photosPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $filename = Str::uuid() . '.' . $photo->getClientOriginalExtension();
                $path = $photo->storeAs('reports/photos', $filename, 'public');
                $photosPaths[] = $path;
            }
        }

        $report = Report::create([
            'titre' => $request->titre,
            'description' => $request->description,
            'type' => $request->type,
            'date_intervention' => $request->date_intervention,
            'lieu' => $request->lieu,
            'probleme_identifie' => $request->probleme_identifie,
            'actions_effectuees' => $request->actions_effectuees,
            'materiels_utilises' => $request->materiels_utilises,
            'etat_equipement' => $request->etat_equipement,
            'recommandations' => $request->recommandations,
            'cout_intervention' => $request->cout_intervention,
            'statut' => 'brouillon',
            'photos' => $photosPaths,
            'user_id' => Auth::id(),
            'event_id' => $request->event_id,
            'project_id' => $request->project_id,
        ]);

        return redirect()->route('reports.show', $report)
            ->with('success', 'Rapport créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report)
    {
        $this->authorize('view', $report);

        $report->load(['user', 'event', 'project']);

        return view('reports.show', compact('report'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Report $report)
    {
        $this->authorize('update', $report);

        $projects = Project::active()->orderBy('nom')->get();
        $events = Event::where('user_id', Auth::id())
            ->where('statut', '!=', 'annule')
            ->orderBy('date_debut', 'desc')
            ->get();

        return view('reports.edit', compact('report', 'projects', 'events'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Report $report)
    {
        $this->authorize('update', $report);

        $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:intervention,maintenance,inspection,reparation,installation,autre',
            'date_intervention' => 'required|date|before_or_equal:today',
            'lieu' => 'required|string|max:255',
            'probleme_identifie' => 'nullable|string',
            'actions_effectuees' => 'required|string',
            'materiels_utilises' => 'nullable|string',
            'etat_equipement' => 'nullable|in:bon,moyen,mauvais,hors_service',
            'recommandations' => 'nullable|string',
            'cout_intervention' => 'nullable|numeric|min:0',
            'event_id' => 'nullable|exists:events,id',
            'project_id' => 'nullable|exists:projects,id',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        // Gestion des nouvelles photos
        $photosPaths = $report->photos ?? [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $filename = Str::uuid() . '.' . $photo->getClientOriginalExtension();
                $path = $photo->storeAs('reports/photos', $filename, 'public');
                $photosPaths[] = $path;
            }
        }

        $report->update([
            'titre' => $request->titre,
            'description' => $request->description,
            'type' => $request->type,
            'date_intervention' => $request->date_intervention,
            'lieu' => $request->lieu,
            'probleme_identifie' => $request->probleme_identifie,
            'actions_effectuees' => $request->actions_effectuees,
            'materiels_utilises' => $request->materiels_utilises,
            'etat_equipement' => $request->etat_equipement,
            'recommandations' => $request->recommandations,
            'cout_intervention' => $request->cout_intervention,
            'photos' => $photosPaths,
            'event_id' => $request->event_id,
            'project_id' => $request->project_id,
        ]);

        return redirect()->route('reports.show', $report)
            ->with('success', 'Rapport mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        $this->authorize('delete', $report);

        // Supprimer les photos
        if ($report->photos) {
            foreach ($report->photos as $photo) {
                Storage::disk('public')->delete($photo);
            }
        }

        $report->delete();

        return redirect()->route('reports.index')
            ->with('success', 'Rapport supprimé avec succès.');
    }

    /**
     * Submit report for validation
     */
    public function submit(Report $report)
    {
        $this->authorize('update', $report);

        if ($report->statut !== 'brouillon') {
            return back()->with('error', 'Seuls les rapports en brouillon peuvent être soumis.');
        }

        $report->submit();

        return back()->with('success', 'Rapport soumis pour validation.');
    }

    /**
     * Validate report (admin/chef only)
     */
    public function validate(Report $report)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isChefEquipe()) {
            abort(403, 'Vous n\'avez pas l\'autorisation de valider ce rapport.');
        }

        if ($report->statut !== 'soumis') {
            return back()->with('error', 'Seuls les rapports soumis peuvent être validés.');
        }

        $report->validate();

        return back()->with('success', 'Rapport validé avec succès.');
    }

    /**
     * Reject report (admin/chef only)
     */
    public function reject(Request $request, Report $report)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isChefEquipe()) {
            abort(403, 'Vous n\'avez pas l\'autorisation de rejeter ce rapport.');
        }

        if ($report->statut !== 'soumis') {
            return back()->with('error', 'Seuls les rapports soumis peuvent être rejetés.');
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ], [
            'reason.required' => 'La raison du rejet est obligatoire.',
        ]);

        $report->reject();

        // Ajouter la raison dans les commentaires (si le champ existe)
        $report->update([
            'commentaires_validation' => $request->reason
        ]);

        return back()->with('success', 'Rapport rejeté.');
    }

    /**
     * Upload photo to report
     */
    public function uploadPhoto(Request $request, Report $report)
    {
        $this->authorize('update', $report);

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $filename = Str::uuid() . '.' . $request->file('photo')->getClientOriginalExtension();
        $path = $request->file('photo')->storeAs('reports/photos', $filename, 'public');

        $photos = $report->photos ?? [];
        $photos[] = $path;
        $report->update(['photos' => $photos]);

        return response()->json([
            'success' => true,
            'message' => 'Photo ajoutée avec succès.',
            'photo_url' => Storage::url($path),
            'photo_path' => $path,
        ]);
    }

    /**
     * Delete photo from report
     */
    public function deletePhoto(Report $report, $photoIndex)
    {
        $this->authorize('update', $report);

        $photos = $report->photos ?? [];

        if (!isset($photos[$photoIndex])) {
            return response()->json([
                'success' => false,
                'message' => 'Photo non trouvée.',
            ], 404);
        }

        // Supprimer le fichier
        Storage::disk('public')->delete($photos[$photoIndex]);

        // Retirer de la liste
        unset($photos[$photoIndex]);
        $photos = array_values($photos); // Réindexer

        $report->update(['photos' => $photos]);

        return response()->json([
            'success' => true,
            'message' => 'Photo supprimée avec succès.',
        ]);
    }

    /**
     * Download report as PDF
     */
    public function downloadPdf(Report $report)
    {
        $this->authorize('view', $report);

        $report->load(['user', 'event', 'project']);

        // Ici vous pouvez utiliser une librairie comme DomPDF ou wkhtmltopdf
        // Pour la démo, on retourne une vue HTML qui peut être imprimée
        return view('reports.pdf', compact('report'));
    }

    /**
     * Get reports statistics
     */
    public function stats()
    {
        $query = Report::query();

        // Filtrage selon le rôle
        if (!Auth::user()->isAdmin() && !Auth::user()->isChefEquipe()) {
            $query->where('user_id', Auth::id());
        }

        $stats = [
            'total' => $query->count(),
            'this_month' => $query->clone()->thisMonth()->count(),
            'validated' => $query->clone()->where('statut', 'valide')->count(),
            'pending' => $query->clone()->where('statut', 'soumis')->count(),
            'draft' => $query->clone()->where('statut', 'brouillon')->count(),
            'by_type' => $query->clone()->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type'),
            'by_status' => $query->clone()->selectRaw('statut, COUNT(*) as count')
                ->groupBy('statut')
                ->pluck('count', 'statut'),
            'total_cost' => $query->clone()->sum('cout_intervention'),
            'average_cost' => $query->clone()->avg('cout_intervention'),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Export reports
     */
    public function export(Request $request)
    {
        $query = Report::with(['user', 'event', 'project']);

        // Filtrage selon le rôle
        if (!Auth::user()->isAdmin() && !Auth::user()->isChefEquipe()) {
            $query->where('user_id', Auth::id());
        }

        // Appliquer les mêmes filtres que l'index
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('statut', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date_intervention', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date_intervention', '<=', $request->date_to);
        }

        $reports = $query->orderBy('date_intervention', 'desc')->get();

        return view('reports.export', compact('reports'));
    }

    /**
     * Duplicate report
     */
    public function duplicate(Report $report)
    {
        $this->authorize('create', Report::class);

        $newReport = $report->replicate();
        $newReport->titre = 'Copie de ' . $report->titre;
        $newReport->statut = 'brouillon';
        $newReport->date_intervention = now()->format('Y-m-d');
        $newReport->photos = null; // Ne pas copier les photos
        $newReport->user_id = Auth::id();
        $newReport->save();

        return redirect()->route('reports.edit', $newReport)
            ->with('success', 'Rapport dupliqué avec succès.');
    }
}
