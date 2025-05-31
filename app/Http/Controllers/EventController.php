<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Event::with(['user', 'project']);

        // Filtrage selon le rôle
        if (!Auth::user()->isAdmin()) {
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

        if ($request->filled('user') && Auth::user()->isAdmin()) {
            $query->where('user_id', $request->user);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date_debut', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date_debut', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('lieu', 'like', "%{$search}%");
            });
        }

        // Tri par date par défaut
        $sortBy = $request->get('sort', 'date_debut');
        $sortDirection = $request->get('direction', 'asc');

        if (in_array($sortBy, ['titre', 'type', 'statut', 'date_debut', 'lieu'])) {
            $query->orderBy($sortBy, $sortDirection);
        }

        $events = $query->paginate(15)->withQueryString();

        // Données pour les filtres
        $projects = Project::orderBy('nom')->get();
        $users = Auth::user()->isAdmin() ? User::orderBy('prenom')->get() : collect();

        return view('events.index', compact('events', 'projects', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $projects = Project::active()->orderBy('nom')->get();
        $users = User::active()->orderBy('prenom')->get();

        return view('events.create', compact('projects', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:intervention,reunion,formation,visite,maintenance,autre',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'lieu' => 'required|string|max:255',
            'participants' => 'nullable|string',
            'materiels_requis' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'project_id' => 'nullable|exists:projects,id',
        ], [
            'titre.required' => 'Le titre est obligatoire.',
            'description.required' => 'La description est obligatoire.',
            'type.required' => 'Le type d\'événement est obligatoire.',
            'date_debut.required' => 'La date de début est obligatoire.',
            'date_fin.required' => 'La date de fin est obligatoire.',
            'date_fin.after' => 'La date de fin doit être postérieure à la date de début.',
            'lieu.required' => 'Le lieu est obligatoire.',
            'user_id.required' => 'L\'assignation à un utilisateur est obligatoire.',
            'user_id.exists' => 'L\'utilisateur sélectionné n\'existe pas.',
            'project_id.exists' => 'Le projet sélectionné n\'existe pas.',
        ]);

        $event = Event::create([
            'titre' => $request->titre,
            'description' => $request->description,
            'type' => $request->type,
            'statut' => 'planifie',
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'lieu' => $request->lieu,
            'participants' => $request->participants,
            'materiels_requis' => $request->materiels_requis,
            'user_id' => $request->user_id,
            'project_id' => $request->project_id,
        ]);

        return redirect()->route('events.show', $event)
            ->with('success', 'Événement créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        $event->load(['user', 'project', 'reports']);

        return view('events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        // Vérifier les permissions
        if (!Auth::user()->isAdmin() && $event->user_id !== Auth::id()) {
            abort(403, 'Vous n\'avez pas l\'autorisation de modifier cet événement.');
        }

        $projects = Project::active()->orderBy('nom')->get();
        $users = User::active()->orderBy('prenom')->get();

        return view('events.edit', compact('event', 'projects', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        // Vérifier les permissions
        if (!Auth::user()->isAdmin() && $event->user_id !== Auth::id()) {
            abort(403, 'Vous n\'avez pas l\'autorisation de modifier cet événement.');
        }

        $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:intervention,reunion,formation,visite,maintenance,autre',
            'statut' => 'required|in:planifie,en_cours,termine,reporte,annule',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'lieu' => 'required|string|max:255',
            'participants' => 'nullable|string',
            'materiels_requis' => 'nullable|string',
            'resultats' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        $event->update($request->only([
            'titre', 'description', 'type', 'statut', 'date_debut', 'date_fin',
            'lieu', 'participants', 'materiels_requis', 'resultats', 'user_id', 'project_id'
        ]));

        return redirect()->route('events.show', $event)
            ->with('success', 'Événement mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        // Vérifier les permissions
        if (!Auth::user()->isAdmin() && $event->user_id !== Auth::id()) {
            abort(403, 'Vous n\'avez pas l\'autorisation de supprimer cet événement.');
        }

        $event->delete();

        return redirect()->route('events.index')
            ->with('success', 'Événement supprimé avec succès.');
    }

    /**
     * Display calendar view
     */
    public function calendar(Request $request)
    {
        $events = Event::with(['user', 'project']);

        // Filtrage selon le rôle
        if (!Auth::user()->isAdmin()) {
            $events->where('user_id', Auth::id());
        }

        // Filtrer par mois si spécifié
        if ($request->filled('month') && $request->filled('year')) {
            $events->whereMonth('date_debut', $request->month)
                   ->whereYear('date_debut', $request->year);
        } else {
            // Par défaut, afficher le mois actuel
            $events->whereMonth('date_debut', now()->month)
                   ->whereYear('date_debut', now()->year);
        }

        $events = $events->orderBy('date_debut')->get();

        return view('events.calendar', compact('events'));
    }

    /**
     * Get calendar data for AJAX requests
     */
    public function calendarData(Request $request)
    {
        $query = Event::with(['user', 'project']);

        // Filtrage selon le rôle
        if (!Auth::user()->isAdmin()) {
            $query->where('user_id', Auth::id());
        }

        // Filtrer par période si spécifiée
        if ($request->filled('start') && $request->filled('end')) {
            $query->whereBetween('date_debut', [
                Carbon::parse($request->start),
                Carbon::parse($request->end)
            ]);
        }

        $events = $query->get();

        // Formater pour FullCalendar
        $calendarEvents = $events->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->titre,
                'start' => $event->date_debut->toISOString(),
                'end' => $event->date_fin->toISOString(),
                'description' => $event->description,
                'location' => $event->lieu,
                'type' => $event->type,
                'status' => $event->statut,
                'backgroundColor' => $this->getEventColor($event->type, $event->statut),
                'borderColor' => $this->getEventColor($event->type, $event->statut),
                'url' => route('events.show', $event),
                'extendedProps' => [
                    'user' => $event->user ? $event->user->prenom . ' ' . $event->user->nom : '',
                    'project' => $event->project ? $event->project->nom : '',
                    'type_label' => $event->type_label,
                    'status_label' => $event->status_label,
                ]
            ];
        });

        return response()->json($calendarEvents);
    }

    /**
     * Update event status via AJAX
     */
    public function updateStatus(Request $request, Event $event)
    {
        // Vérifier les permissions
        if (!Auth::user()->isAdmin() && $event->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'avez pas l\'autorisation de modifier cet événement.'
            ], 403);
        }

        $request->validate([
            'statut' => 'required|in:planifie,en_cours,termine,reporte,annule',
        ]);

        $event->update(['statut' => $request->statut]);

        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour avec succès.',
            'event' => [
                'id' => $event->id,
                'statut' => $event->statut,
                'status_label' => $event->status_label,
            ]
        ]);
    }

    /**
     * Get upcoming events
     */
    public function upcoming(Request $request)
    {
        $limit = $request->get('limit', 5);

        $query = Event::with(['user', 'project'])
            ->where('date_debut', '>', now())
            ->where('statut', '!=', 'annule');

        // Filtrage selon le rôle
        if (!Auth::user()->isAdmin()) {
            $query->where('user_id', Auth::id());
        }

        $events = $query->orderBy('date_debut')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'events' => $events->map(function ($event) {
                return [
                    'id' => $event->id,
                    'titre' => $event->titre,
                    'type' => $event->type,
                    'type_label' => $event->type_label,
                    'date_debut' => $event->date_debut->format('d/m/Y H:i'),
                    'lieu' => $event->lieu,
                    'url' => route('events.show', $event),
                ];
            })
        ]);
    }

    /**
     * Get color for event based on type and status
     */
    private function getEventColor($type, $status)
    {
        // Couleur selon le statut d'abord
        if ($status === 'annule') return '#dc3545'; // Rouge
        if ($status === 'termine') return '#198754'; // Vert
        if ($status === 'en_cours') return '#0d6efd'; // Bleu
        if ($status === 'reporte') return '#ffc107'; // Jaune

        // Sinon couleur selon le type
        return match($type) {
            'intervention' => '#dc3545', // Rouge
            'reunion' => '#0d6efd', // Bleu
            'formation' => '#198754', // Vert
            'visite' => '#17a2b8', // Cyan
            'maintenance' => '#ffc107', // Jaune
            default => '#6c757d' // Gris
        };
    }

    /**
     * Get events statistics
     */
    public function stats()
    {
        $query = Event::query();

        // Filtrage selon le rôle
        if (!Auth::user()->isAdmin()) {
            $query->where('user_id', Auth::id());
        }

        $stats = [
            'total' => $query->count(),
            'today' => $query->clone()->whereDate('date_debut', today())->count(),
            'this_week' => $query->clone()->whereBetween('date_debut', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            'upcoming' => $query->clone()->where('date_debut', '>', now())->count(),
            'by_type' => $query->clone()->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type'),
            'by_status' => $query->clone()->selectRaw('statut, COUNT(*) as count')
                ->groupBy('statut')
                ->pluck('count', 'statut'),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
}
