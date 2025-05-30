<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log; // ← CORRECTION 1 : Import correct pour Log
use Illuminate\Http\Response;
use App\Models\Report;
use App\Models\PieceJointe;
use App\Models\Task;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
// PLUS D'IMPORTS PDF - Supprimés pour éviter les erreurs

class ReportController extends Controller
{
    /**
     * Afficher la liste des rapports
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Report::with('user', 'task', 'event', 'piecesJointes');

        // Filtrage selon le rôle
        if ($user->role === 'technicien') {
            $query->where('id_utilisateur', $user->id);
        }

        // Filtres de recherche
        if ($request->filled('utilisateur') && $user->role === 'admin') {
            $query->where('id_utilisateur', $request->utilisateur);
        }

        if ($request->filled('type_intervention')) {
            $query->where('type_intervention', 'like', "%{$request->type_intervention}%");
        }

        if ($request->filled('lieu')) {
            $query->where('lieu', 'like', "%{$request->lieu}%");
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('date_intervention', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date_intervention', '<=', $request->date_fin);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                  ->orWhere('lieu', 'like', "%{$search}%")
                  ->orWhere('type_intervention', 'like', "%{$search}%")
                  ->orWhere('actions', 'like', "%{$search}%")
                  ->orWhere('resultats', 'like', "%{$search}%");
            });
        }

        // Tri par défaut : rapports les plus récents
        $sortBy = $request->get('sort', 'date_creation');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $reports = $query->paginate(15);

        // Données pour les filtres
        $users = $user->role === 'admin' ? User::where('statut', 'actif')->get() : collect();

        // Types d'intervention les plus courants
        $interventionTypes = Report::selectRaw('type_intervention, count(*) as count')
                                  ->groupBy('type_intervention')
                                  ->orderBy('count', 'desc')
                                  ->limit(10)
                                  ->pluck('type_intervention');

        return view('reports.index', compact('reports', 'users', 'interventionTypes'));
    }

    /**
     * Afficher le formulaire de création d'un rapport
     */
    public function create(Request $request)
    {
        // Pré-remplir avec tâche ou événement si fourni
        $task = $request->has('task_id') ? Task::find($request->task_id) : null;
        $event = $request->has('event_id') ? Event::find($request->event_id) : null;

        $tasks = Task::where('id_utilisateur', Auth::id())
                    ->whereIn('statut', ['en_cours', 'termine'])
                    ->orderBy('date_echeance', 'desc')
                    ->get();

        $events = Event::where('id_organisateur', Auth::id())
                      ->orWhereHas('participants', function($q) {
                          $q->where('id_utilisateur', Auth::id());
                      })
                      ->where('statut', 'termine')
                      ->orderBy('date_debut', 'desc')
                      ->get();

        return view('reports.create', compact('task', 'event', 'tasks', 'events'));
    }

    /**
     * Enregistrer un nouveau rapport
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titre' => 'required|string|max:100',
            'date_intervention' => 'required|date|before_or_equal:today',
            'lieu' => 'required|string|max:100',
            'type_intervention' => 'required|string|max:50',
            'actions' => 'required|string',
            'resultats' => 'required|string',
            'problemes' => 'nullable|string',
            'recommandations' => 'nullable|string',
            'id_tache' => 'nullable|exists:tasks,id',
            'id_evenement' => 'nullable|exists:events,id',
            'pieces_jointes' => 'nullable|array|max:5',
            'pieces_jointes.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,gif',
        ], [
            'titre.required' => 'Le titre est obligatoire.',
            'date_intervention.required' => 'La date d\'intervention est obligatoire.',
            'date_intervention.before_or_equal' => 'La date d\'intervention ne peut pas être future.',
            'lieu.required' => 'Le lieu est obligatoire.',
            'type_intervention.required' => 'Le type d\'intervention est obligatoire.',
            'actions.required' => 'La description des actions est obligatoire.',
            'resultats.required' => 'La description des résultats est obligatoire.',
            'pieces_jointes.*.max' => 'Chaque fichier ne peut pas dépasser 10 MB.',
            'pieces_jointes.*.mimes' => 'Types de fichiers autorisés: PDF, DOC, DOCX, JPG, PNG, GIF.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $report = Report::create([
            'titre' => $request->titre,
            'date_intervention' => $request->date_intervention,
            'lieu' => $request->lieu,
            'type_intervention' => $request->type_intervention,
            'actions' => $request->actions,
            'resultats' => $request->resultats,
            'problemes' => $request->problemes,
            'recommandations' => $request->recommandations,
            'id_utilisateur' => Auth::id(),
            'id_tache' => $request->id_tache,
            'id_evenement' => $request->id_evenement,
        ]);

        // Traitement des pièces jointes
        if ($request->hasFile('pieces_jointes')) {
            foreach ($request->file('pieces_jointes') as $file) {
                $this->storeAttachment($file, $report);
            }
        }

        // Log de l'action
        $this->logAction('CREATION', "Création du rapport: {$report->titre}");

        return redirect()->route('reports.index')
                        ->with('success', 'Rapport créé avec succès.');
    }

    /**
     * Afficher les détails d'un rapport
     */
    public function show(Report $report)
    {
        $this->authorize('view', $report);

        $report->load('user', 'task.project', 'event', 'piecesJointes');

        return view('reports.show', compact('report'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Report $report)
    {
        $this->authorize('update', $report);

        $tasks = Task::where('id_utilisateur', Auth::id())
                    ->whereIn('statut', ['en_cours', 'termine'])
                    ->orderBy('date_echeance', 'desc')
                    ->get();

        $events = Event::where('id_organisateur', Auth::id())
                      ->orWhereHas('participants', function($q) {
                          $q->where('id_utilisateur', Auth::id());
                      })
                      ->where('statut', 'termine')
                      ->orderBy('date_debut', 'desc')
                      ->get();

        return view('reports.edit', compact('report', 'tasks', 'events'));
    }

    /**
     * Mettre à jour un rapport
     */
    public function update(Request $request, Report $report)
    {
        $this->authorize('update', $report);

        $validator = Validator::make($request->all(), [
            'titre' => 'required|string|max:100',
            'date_intervention' => 'required|date|before_or_equal:today',
            'lieu' => 'required|string|max:100',
            'type_intervention' => 'required|string|max:50',
            'actions' => 'required|string',
            'resultats' => 'required|string',
            'problemes' => 'nullable|string',
            'recommandations' => 'nullable|string',
            'id_tache' => 'nullable|exists:tasks,id',
            'id_evenement' => 'nullable|exists:events,id',
            'nouvelles_pieces_jointes' => 'nullable|array|max:5',
            'nouvelles_pieces_jointes.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,gif',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $report->update([
            'titre' => $request->titre,
            'date_intervention' => $request->date_intervention,
            'lieu' => $request->lieu,
            'type_intervention' => $request->type_intervention,
            'actions' => $request->actions,
            'resultats' => $request->resultats,
            'problemes' => $request->problemes,
            'recommandations' => $request->recommandations,
            'id_tache' => $request->id_tache,
            'id_evenement' => $request->id_evenement,
        ]);

        // Traitement des nouvelles pièces jointes
        if ($request->hasFile('nouvelles_pieces_jointes')) {
            foreach ($request->file('nouvelles_pieces_jointes') as $file) {
                $this->storeAttachment($file, $report);
            }
        }

        $this->logAction('MODIFICATION', "Modification du rapport: {$report->titre}");

        return redirect()->route('reports.show', $report)
                        ->with('success', 'Rapport mis à jour avec succès.');
    }

    /**
     * Supprimer un rapport
     */
    public function destroy(Report $report)
    {
        $this->authorize('delete', $report);

        $reportTitle = $report->titre;

        // Supprimer les pièces jointes
        foreach ($report->piecesJointes as $attachment) {
            if (Storage::disk('public')->exists($attachment->chemin)) {
                Storage::disk('public')->delete($attachment->chemin);
            }
            $attachment->delete();
        }

        $report->delete();

        $this->logAction('SUPPRESSION', "Suppression du rapport: {$reportTitle}");

        return redirect()->route('reports.index')
                        ->with('success', 'Rapport supprimé avec succès.');
    }

    /**
     * CORRECTION 2 : Télécharger une pièce jointe - VERSION NATIVE
     */
    public function downloadAttachment(PieceJointe $attachment)
    {
        $report = $attachment->report;
        $this->authorize('view', $report);

        // Vérifier que le fichier existe
        if (!Storage::disk('public')->exists($attachment->chemin)) {
            return redirect()->back()->with('error', 'Fichier non trouvé.');
        }

        $this->logAction('TELECHARGEMENT', "Téléchargement de la pièce jointe: {$attachment->nom_fichier}");

        // SOLUTION NATIVE - Fonctionne toujours
        try {
            $filePath = Storage::disk('public')->path($attachment->chemin);

            if (!file_exists($filePath)) {
                return redirect()->back()->with('error', 'Fichier introuvable sur le serveur.');
            }

            return response()->download($filePath, $attachment->nom_fichier, [
                'Content-Type' => $attachment->type_fichier ?? 'application/octet-stream',
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur téléchargement fichier: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du téléchargement.');
        }
    }

    /**
     * Supprimer une pièce jointe
     */
    public function deleteAttachment(PieceJointe $attachment)
    {
        $report = $attachment->report;
        $this->authorize('update', $report);

        if (Storage::disk('public')->exists($attachment->chemin)) {
            Storage::disk('public')->delete($attachment->chemin);
        }

        $fileName = $attachment->nom_fichier;
        $attachment->delete();

        $this->logAction('SUPPRESSION', "Suppression de la pièce jointe: {$fileName}");

        return response()->json([
            'success' => true,
            'message' => 'Pièce jointe supprimée avec succès'
        ]);
    }

    /**
     * CORRECTION 3 : Export en HTML formaté (Alternative au PDF)
     * FONCTIONNE SANS AUCUN PACKAGE EXTERNE
     */
    public function exportHTML(Report $report)
    {
        $this->authorize('view', $report);

        $report->load('user', 'task.project', 'event', 'piecesJointes');

        // Générer le HTML avec style CSS intégré
        $html = $this->generateReportHTML($report);
        $filename = 'rapport_' . $report->id . '_' . date('Y-m-d') . '.html';

        $this->logAction('EXPORT', "Export HTML du rapport: {$report->titre}");

        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Export en format texte simple (Alternative PDF #2)
     */
    public function exportText(Report $report)
    {
        $this->authorize('view', $report);

        $report->load('user', 'task.project', 'event', 'piecesJointes');

        $content = $this->generateReportText($report);
        $filename = 'rapport_' . $report->id . '_' . date('Y-m-d') . '.txt';

        $this->logAction('EXPORT', "Export TXT du rapport: {$report->titre}");

        return response($content)
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Afficher le rapport dans le navigateur pour impression
     */
    public function printView(Report $report)
    {
        $this->authorize('view', $report);

        $report->load('user', 'task.project', 'event', 'piecesJointes');

        $this->logAction('IMPRESSION', "Affichage impression du rapport: {$report->titre}");

        return view('reports.print', compact('report'));
    }

    /**
     * Statistiques des rapports
     */
    public function statistics()
    {
        $user = Auth::user();
        $query = Report::query();

        if ($user->role === 'technicien') {
            $query->where('id_utilisateur', $user->id);
        }

        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;

        $stats = [
            'total_reports' => $query->count(),
            'this_year' => $query->whereYear('date_creation', $currentYear)->count(),
            'this_month' => $query->whereYear('date_creation', $currentYear)
                                 ->whereMonth('date_creation', $currentMonth)->count(),
            'last_30_days' => $query->where('date_creation', '>=', Carbon::now()->subDays(30))->count(),
        ];

        // Rapports par type d'intervention
        $reportsByType = $query->selectRaw('type_intervention, count(*) as count')
                              ->groupBy('type_intervention')
                              ->orderBy('count', 'desc')
                              ->get();

        // Rapports par mois (12 derniers mois)
        $reportsByMonth = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = $query->whereYear('date_creation', $date->year)
                          ->whereMonth('date_creation', $date->month)
                          ->count();

            $reportsByMonth->push([
                'month' => $date->format('M Y'),
                'count' => $count
            ]);
        }

        // Rapports par technicien (admin seulement)
        $reportsByUser = collect();
        if ($user->role === 'admin') {
            $reportsByUser = User::withCount('reports')
                                ->where('statut', 'actif')
                                ->where('role', 'technicien')
                                ->orderBy('reports_count', 'desc')
                                ->get();
        }

        return view('reports.statistics', compact(
            'stats',
            'reportsByType',
            'reportsByMonth',
            'reportsByUser'
        ));
    }

    /**
     * Sauvegarder une pièce jointe
     */
    private function storeAttachment($file, $report)
    {
        try {
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $size = $file->getSize();
            $mimeType = $file->getMimeType();

            // Générer un nom unique pour le fichier
            $fileName = time() . '_' . uniqid() . '.' . $extension;
            $path = $file->storeAs('reports/' . $report->id, $fileName, 'public');

            PieceJointe::create([
                'nom_fichier' => $originalName,
                'type_fichier' => $mimeType,
                'taille' => $size,
                'chemin' => $path,
                'id_rapport' => $report->id,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Erreur sauvegarde pièce jointe: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Générer le HTML formaté pour l'export
     */
    private function generateReportHTML($report)
    {
        $html = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport d\'intervention - ' . htmlspecialchars($report->titre) . '</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #2c5aa0;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2c5aa0;
            margin: 0;
            font-size: 24px;
        }
        .header h2 {
            color: #666;
            margin: 5px 0;
            font-size: 18px;
        }
        .info-section {
            background: #f8f9fa;
            border-left: 4px solid #2c5aa0;
            padding: 15px;
            margin: 20px 0;
        }
        .info-section h3 {
            color: #2c5aa0;
            margin-top: 0;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 10px 0;
        }
        .info-item {
            padding: 5px 0;
        }
        .info-item strong {
            color: #2c5aa0;
        }
        .content-section {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .signature {
            margin-top: 50px;
            text-align: right;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>OFFICE RÉGIONAL DE MISE EN VALEUR AGRICOLE DU TADLA</h1>
        <h2>Rapport d\'Intervention Technique</h2>
        <p>Généré le ' . Carbon::now()->format('d/m/Y à H:i') . '</p>
    </div>

    <div class="info-section">
        <h3>Informations générales</h3>
        <div class="info-grid">
            <div class="info-item"><strong>Titre :</strong> ' . htmlspecialchars($report->titre) . '</div>
            <div class="info-item"><strong>Date d\'intervention :</strong> ' . Carbon::parse($report->date_intervention)->format('d/m/Y') . '</div>
            <div class="info-item"><strong>Lieu :</strong> ' . htmlspecialchars($report->lieu) . '</div>
            <div class="info-item"><strong>Type :</strong> ' . htmlspecialchars($report->type_intervention) . '</div>
            <div class="info-item"><strong>Technicien :</strong> ' . htmlspecialchars($report->user->prenom . ' ' . $report->user->nom) . '</div>
            <div class="info-item"><strong>Date de création :</strong> ' . Carbon::parse($report->date_creation)->format('d/m/Y à H:i') . '</div>
        </div>';

        if ($report->task) {
            $html .= '<div class="info-item"><strong>Tâche associée :</strong> ' . htmlspecialchars($report->task->titre) . '</div>';
        }

        if ($report->event) {
            $html .= '<div class="info-item"><strong>Événement associé :</strong> ' . htmlspecialchars($report->event->titre) . '</div>';
        }

        $html .= '</div>

    <div class="content-section">
        <h3>Actions réalisées</h3>
        <p>' . nl2br(htmlspecialchars($report->actions)) . '</p>
    </div>

    <div class="content-section">
        <h3>Résultats obtenus</h3>
        <p>' . nl2br(htmlspecialchars($report->resultats)) . '</p>
    </div>';

        if ($report->problemes) {
            $html .= '<div class="content-section">
        <h3>Problèmes rencontrés</h3>
        <p>' . nl2br(htmlspecialchars($report->problemes)) . '</p>
    </div>';
        }

        if ($report->recommandations) {
            $html .= '<div class="content-section">
        <h3>Recommandations</h3>
        <p>' . nl2br(htmlspecialchars($report->recommandations)) . '</p>
    </div>';
        }

        if ($report->piecesJointes->count() > 0) {
            $html .= '<div class="content-section">
        <h3>Pièces jointes</h3>
        <ul>';
            foreach ($report->piecesJointes as $attachment) {
                $html .= '<li>' . htmlspecialchars($attachment->nom_fichier) . ' (' . $this->formatFileSize($attachment->taille) . ')</li>';
            }
            $html .= '</ul>
    </div>';
        }

        $html .= '<div class="signature">
        <p><strong>Signature du technicien :</strong> _____________________</p>
        <p><em>Ce rapport a été généré automatiquement par le système PlanifTech</em></p>
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Générer le contenu texte pour l'export
     */
    private function generateReportText($report)
    {
        $content = "===========================================\n";
        $content .= "OFFICE RÉGIONAL DE MISE EN VALEUR AGRICOLE DU TADLA\n";
        $content .= "RAPPORT D'INTERVENTION TECHNIQUE\n";
        $content .= "===========================================\n\n";

        $content .= "INFORMATIONS GÉNÉRALES\n";
        $content .= "----------------------\n";
        $content .= "Titre : " . $report->titre . "\n";
        $content .= "Date d'intervention : " . Carbon::parse($report->date_intervention)->format('d/m/Y') . "\n";
        $content .= "Lieu : " . $report->lieu . "\n";
        $content .= "Type d'intervention : " . $report->type_intervention . "\n";
        $content .= "Technicien : " . $report->user->prenom . ' ' . $report->user->nom . "\n";
        $content .= "Date de création : " . Carbon::parse($report->date_creation)->format('d/m/Y à H:i') . "\n\n";

        $content .= "ACTIONS RÉALISÉES\n";
        $content .= "-----------------\n";
        $content .= $report->actions . "\n\n";

        $content .= "RÉSULTATS OBTENUS\n";
        $content .= "-----------------\n";
        $content .= $report->resultats . "\n\n";

        if ($report->problemes) {
            $content .= "PROBLÈMES RENCONTRÉS\n";
            $content .= "--------------------\n";
            $content .= $report->problemes . "\n\n";
        }

        if ($report->recommandations) {
            $content .= "RECOMMANDATIONS\n";
            $content .= "---------------\n";
            $content .= $report->recommandations . "\n\n";
        }

        $content .= "===========================================\n";
        $content .= "Rapport généré le " . Carbon::now()->format('d/m/Y à H:i') . "\n";
        $content .= "Système PlanifTech - ORMVAT\n";

        return $content;
    }

    /**
     * Formater la taille de fichier
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * API pour obtenir les rapports récents
     */
    public function getRecentReports()
    {
        $user = Auth::user();
        $query = Report::with('user');

        if ($user->role === 'technicien') {
            $query->where('id_utilisateur', $user->id);
        }

        $reports = $query->orderBy('date_creation', 'desc')
                        ->limit(10)
                        ->get();

        return response()->json($reports);
    }

    /**
     * Recherche avancée de rapports
     */
    public function search(Request $request)
    {
        $user = Auth::user();
        $query = Report::with('user', 'task', 'event');

        if ($user->role === 'technicien') {
            $query->where('id_utilisateur', $user->id);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                  ->orWhere('lieu', 'like', "%{$search}%")
                  ->orWhere('type_intervention', 'like', "%{$search}%")
                  ->orWhere('actions', 'like', "%{$search}%")
                  ->orWhere('resultats', 'like', "%{$search}%");
            });
        }

        $reports = $query->orderBy('date_creation', 'desc')
                        ->paginate(20);

        return response()->json($reports);
    }

    /**
     * CORRECTION 4 : Log des actions - Version corrigée
     */
    private function logAction($type, $description)
    {
        try {
            // Vérifier si le modèle Journal existe
            if (class_exists('\App\Models\Journal')) {
                \App\Models\Journal::create([
                    'date' => now(),
                    'type_action' => $type,
                    'description' => $description,
                    'utilisateur_id' => Auth::id(),
                    'adresse_ip' => request()->ip(),
                ]);
            } else {
                // Fallback : utiliser les logs Laravel standards
                Log::info("Action {$type}: {$description}", [
                    'user_id' => Auth::id(),
                    'ip' => request()->ip(),
                    'timestamp' => now()
                ]);
            }
        } catch (\Exception $e) {
            // Log silencieux pour ne pas casser l'application
            Log::warning('Erreur lors du logging: ' . $e->getMessage());
        }
    }
}
