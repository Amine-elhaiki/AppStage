<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Event;
use App\Models\User;
use App\Models\Project;
use App\Models\Participation;
use Carbon\Carbon;

class EventController extends Controller
{
    /**
     * Afficher la liste des événements
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Event::with('organisateur', 'project');

        // Filtrage selon le rôle
        if ($user->role === 'technicien') {
            $query->where('id_organisateur', $user->id)
                  ->orWhereHas('participants', function($q) use ($user) {
                      $q->where('id_utilisateur', $user->id);
                  });
        }

        // Filtres de recherche
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('organisateur') && $user->role === 'admin') {
            $query->where('id_organisateur', $request->organisateur);
        }

        if ($request->filled('projet')) {
            $query->where('id_projet', $request->projet);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('date_debut', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date_debut', '<=', $request->date_fin);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('lieu', 'like', "%{$search}%");
            });
        }

        // Tri par défaut : événements à venir d'abord
        $sortBy = $request->get('sort', 'date_debut');
        $sortOrder = $request->get('order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $events = $query->paginate(12);

        // Données pour les filtres
        $organisateurs = $user->role === 'admin' ? User::where('statut', 'actif')->get() : collect();
        $projects = Project::where('statut', '!=', 'termine')->get();

        return view('events.index', compact('events', 'organisateurs', 'projects'));
    }

    /**
     * Afficher le calendrier des événements
     */
    public function calendar(Request $request)
    {
        $user = Auth::user();

        // Données pour les filtres
        $users = $user->role === 'admin' ? User::where('statut', 'actif')->get() : collect();
        $projects = Project::where('statut', '!=', 'termine')->get();

        return view('events.calendar', compact('users', 'projects'));
    }

    /**
     * API pour obtenir les événements du calendrier
     */
    public function getCalendarEvents(Request $request)
    {
        $user = Auth::user();
        $query = Event::with('organisateur', 'project');

        // Filtrage selon le rôle
        if ($user->role === 'technicien') {
            $query->where('id_organisateur', $user->id)
                  ->orWhereHas('participants', function($q) use ($user) {
                      $q->where('id_utilisateur', $user->id);
                  });
        }

        // Filtres optionnels
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('organisateur') && $user->role === 'admin') {
            $query->where('id_organisateur', $request->organisateur);
        }

        if ($request->filled('projet')) {
            $query->where('id_projet', $request->projet);
        }

        // Période pour le calendrier
        if ($request->filled('start') && $request->filled('end')) {
            $query->whereBetween('date_debut', [
                Carbon::parse($request->start)->startOfDay(),
                Carbon::parse($request->end)->endOfDay()
            ]);
        }

        $events = $query->get()->map(function($event) {
            $color = $this->getEventColor($event->type, $event->statut);

            return [
                'id' => $event->id,
                'title' => $event->titre,
                'start' => $event->date_debut->format('Y-m-d H:i:s'),
                'end' => $event->date_fin->format('Y-m-d H:i:s'),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => '#ffffff',
                'url' => route('events.show', $event->id),
                'extendedProps' => [
                    'type' => $event->type,
                    'status' => $event->statut,
                    'location' => $event->lieu,
                    'organizer' => $event->organisateur->nom . ' ' . $event->organisateur->prenom,
                    'project' => $event->project ? $event->project->nom : null
                ]
            ];
        });

        return response()->json($events);
    }

    /**
     * Afficher le formulaire de création d'un événement
     */
    public function create()
    {
        $users = User::where('statut', 'actif')->get();
        $projects = Project::where('statut', '!=', 'termine')->get();

        return view('events.create', compact('users', 'projects'));
    }

    /**
     * Enregistrer un nouvel événement
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titre' => 'required|string|max:100',
            'description' => 'required|string',
            'type' => 'required|in:intervention,reunion,formation,visite',
            'date_debut' => 'required|date|after_or_equal:now',
            'date_fin' => 'required|date|after:date_debut',
            'lieu' => 'required|string|max:100',
            'coordonnees_gps' => 'nullable|string|max:50',
            'priorite' => 'required|in:normale,haute,urgente',
            'id_projet' => 'nullable|exists:projects,id',
            'participants' => 'nullable|array',
            'participants.*' => 'exists:users,id',
        ], [
            'titre.required' => 'Le titre est obligatoire.',
            'description.required' => 'La description est obligatoire.',
            'date_debut.required' => 'La date de début est obligatoire.',
            'date_debut.after_or_equal' => 'La date de début ne peut pas être antérieure à maintenant.',
            'date_fin.required' => 'La date de fin est obligatoire.',
            'date_fin.after' => 'La date de fin doit être postérieure à la date de début.',
            'lieu.required' => 'Le lieu est obligatoire.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $event = Event::create([
            'titre' => $request->titre,
            'description' => $request->description,
            'type' => $request->type,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'lieu' => $request->lieu,
            'coordonnees_gps' => $request->coordonnees_gps,
            'statut' => 'planifie',
            'priorite' => $request->priorite,
            'id_organisateur' => Auth::id(),
            'id_projet' => $request->id_projet,
        ]);

        // Ajouter les participants
        if ($request->has('participants') && is_array($request->participants)) {
            foreach ($request->participants as $participantId) {
                Participation::create([
                    'id_evenement' => $event->id,
                    'id_utilisateur' => $participantId,
                    'statut_presence' => 'invite',
                ]);
            }
        }

        // Log de l'action
        $this->logAction('CREATION', "Création de l'événement: {$event->titre}");

        return redirect()->route('events.index')
                        ->with('success', 'Événement créé avec succès.');
    }

    /**
     * Afficher les détails d'un événement
     */
    public function show(Event $event)
    {
        $this->authorize('view', $event);

        $event->load('organisateur', 'project', 'participants.user', 'tasks');

        // Statistiques de participation
        $participationStats = [
            'total' => $event->participants->count(),
            'confirme' => $event->participants->where('statut_presence', 'confirme')->count(),
            'decline' => $event->participants->where('statut_presence', 'decline')->count(),
            'en_attente' => $event->participants->where('statut_presence', 'invite')->count(),
        ];

        return view('events.show', compact('event', 'participationStats'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Event $event)
    {
        $this->authorize('update', $event);

        $users = User::where('statut', 'actif')->get();
        $projects = Project::where('statut', '!=', 'termine')->get();
        $currentParticipants = $event->participants->pluck('id_utilisateur')->toArray();

        return view('events.edit', compact('event', 'users', 'projects', 'currentParticipants'));
    }

    /**
     * Mettre à jour un événement
     */
    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $validator = Validator::make($request->all(), [
            'titre' => 'required|string|max:100',
            'description' => 'required|string',
            'type' => 'required|in:intervention,reunion,formation,visite',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'lieu' => 'required|string|max:100',
            'coordonnees_gps' => 'nullable|string|max:50',
            'statut' => 'required|in:planifie,en_cours,termine,annule,reporte',
            'priorite' => 'required|in:normale,haute,urgente',
            'id_projet' => 'nullable|exists:projects,id',
            'participants' => 'nullable|array',
            'participants.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $oldStatus = $event->statut;

        $event->update([
            'titre' => $request->titre,
            'description' => $request->description,
            'type' => $request->type,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'lieu' => $request->lieu,
            'coordonnees_gps' => $request->coordonnees_gps,
            'statut' => $request->statut,
            'priorite' => $request->priorite,
            'id_projet' => $request->id_projet,
        ]);

        // Mettre à jour les participants
        $event->participants()->delete();

        if ($request->has('participants') && is_array($request->participants)) {
            foreach ($request->participants as $participantId) {
                Participation::create([
                    'id_evenement' => $event->id,
                    'id_utilisateur' => $participantId,
                    'statut_presence' => 'invite',
                ]);
            }
        }

        // Log si changement de statut
        if ($oldStatus !== $request->statut) {
            $this->logAction('MODIFICATION', "Changement statut événement '{$event->titre}': {$oldStatus} → {$request->statut}");
        }

        return redirect()->route('events.show', $event)
                        ->with('success', 'Événement mis à jour avec succès.');
    }

    /**
     * Supprimer un événement
     */
    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);

        $eventTitle = $event->titre;

        // Supprimer les participations
        $event->participants()->delete();

        // Supprimer l'événement
        $event->delete();

        $this->logAction('SUPPRESSION', "Suppression de l'événement: {$eventTitle}");

        return redirect()->route('events.index')
                        ->with('success', 'Événement supprimé avec succès.');
    }

    /**
     * Confirmer/Décliner la participation à un événement
     */
    public function updateParticipation(Request $request, Event $event)
    {
        $validator = Validator::make($request->all(), [
            'statut' => 'required|in:confirme,decline',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Données invalides'], 400);
        }

        $participation = Participation::where('id_evenement', $event->id)
                                    ->where('id_utilisateur', Auth::id())
                                    ->first();

        if (!$participation) {
            return response()->json(['error' => 'Participation non trouvée'], 404);
        }

        $participation->statut_presence = $request->statut;
        $participation->save();

        $statusText = $request->statut === 'confirme' ? 'confirmée' : 'déclinée';

        $this->logAction('MODIFICATION', "Participation {$statusText} pour l'événement: {$event->titre}");

        return response()->json([
            'success' => true,
            'message' => "Participation {$statusText} avec succès",
            'status' => $request->statut
        ]);
    }

    /**
     * Marquer la présence effective à un événement
     */
    public function markAttendance(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $validator = Validator::make($request->all(), [
            'participants' => 'required|array',
            'participants.*' => 'in:present,absent',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Données invalides'], 400);
        }

        foreach ($request->participants as $userId => $attendance) {
            Participation::where('id_evenement', $event->id)
                         ->where('id_utilisateur', $userId)
                         ->update(['statut_presence' => $attendance]);
        }

        $this->logAction('MODIFICATION', "Présences mises à jour pour l'événement: {$event->titre}");

        return response()->json([
            'success' => true,
            'message' => 'Présences mises à jour avec succès'
        ]);
    }

    /**
     * Obtenir les événements d'un utilisateur pour une période
     */
    public function getUserEvents(Request $request)
    {
        $user = Auth::user();
        $start = Carbon::parse($request->get('start', Carbon::now()->startOfMonth()));
        $end = Carbon::parse($request->get('end', Carbon::now()->endOfMonth()));

        $events = Event::where(function($query) use ($user) {
                            $query->where('id_organisateur', $user->id)
                                  ->orWhereHas('participants', function($q) use ($user) {
                                      $q->where('id_utilisateur', $user->id);
                                  });
                        })
                        ->whereBetween('date_debut', [$start, $end])
                        ->with('organisateur', 'project')
                        ->orderBy('date_debut')
                        ->get();

        return response()->json($events);
    }

    /**
     * Couleur des événements selon type et statut
     */
    private function getEventColor($type, $status)
    {
        if ($status === 'annule') {
            return '#6c757d'; // Gris
        }

        if ($status === 'termine') {
            return '#28a745'; // Vert
        }

        $typeColors = [
            'intervention' => '#dc3545', // Rouge
            'reunion' => '#007bff',      // Bleu
            'formation' => '#28a745',    // Vert
            'visite' => '#ffc107'        // Jaune
        ];

        return $typeColors[$type] ?? '#6c757d';
    }

    /**
     * Log des actions
     */
    private function logAction($type, $description)
    {
        \App\Models\Journal::create([
            'date' => now(),
            'type_action' => $type,
            'description' => $description,
            'utilisateur_id' => Auth::id(),
            'adresse_ip' => request()->ip(),
        ]);
    }
}
