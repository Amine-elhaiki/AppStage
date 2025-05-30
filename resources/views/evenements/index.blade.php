@extends('layouts.app')

@section('title', 'Gestion des événements - PlanifTech ORMVAT')

@section('content')
<div class="container-fluid py-4">
    <!-- En-tête -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-dark">
                        <i class="fas fa-calendar-alt me-2 text-primary"></i>
                        Gestion des événements
                    </h1>
                    <p class="text-muted mb-0">
                        Planifiez et organisez les réunions, interventions et formations ORMVAT
                    </p>
                </div>
                <div class="btn-group" role="group">
                    <a href="{{ route('events.calendar') }}" class="btn btn-outline-primary">
                        <i class="fas fa-calendar me-2"></i>Vue calendrier
                    </a>
                    <a href="{{ route('events.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Nouvel événement
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-calendar-day fa-2x opacity-75"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0 fw-bold">{{ $stats['today_events'] ?? 0 }}</h4>
                            <p class="mb-0 opacity-75">Aujourd'hui</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-calendar-week fa-2x opacity-75"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0 fw-bold">{{ $stats['week_events'] ?? 0 }}</h4>
                            <p class="mb-0 opacity-75">Cette semaine</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-users fa-2x opacity-75"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0 fw-bold">{{ $stats['my_events'] ?? 0 }}</h4>
                            <p class="mb-0 opacity-75">Mes événements</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock fa-2x opacity-75"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0 fw-bold">{{ $stats['upcoming_events'] ?? 0 }}</h4>
                            <p class="mb-0 opacity-75">À venir</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('events.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Recherche</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="search" name="search"
                               value="{{ request('search') }}" placeholder="Titre, lieu, organisateur...">
                    </div>
                </div>

                <div class="col-md-2">
                    <label for="type" class="form-label">Type</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">Tous les types</option>
                        <option value="intervention" {{ request('type') === 'intervention' ? 'selected' : '' }}>
                            🔧 Intervention
                        </option>
                        <option value="reunion" {{ request('type') === 'reunion' ? 'selected' : '' }}>
                            👥 Réunion
                        </option>
                        <option value="formation" {{ request('type') === 'formation' ? 'selected' : '' }}>
                            📚 Formation
                        </option>
                        <option value="visite" {{ request('type') === 'visite' ? 'selected' : '' }}>
                            🏛️ Visite
                        </option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="statut" class="form-label">Statut</label>
                    <select class="form-select" id="statut" name="statut">
                        <option value="">Tous les statuts</option>
                        <option value="planifie" {{ request('statut') === 'planifie' ? 'selected' : '' }}>Planifié</option>
                        <option value="en_cours" {{ request('statut') === 'en_cours' ? 'selected' : '' }}>En cours</option>
                        <option value="termine" {{ request('statut') === 'termine' ? 'selected' : '' }}>Terminé</option>
                        <option value="annule" {{ request('statut') === 'annule' ? 'selected' : '' }}>Annulé</option>
                        <option value="reporte" {{ request('statut') === 'reporte' ? 'selected' : '' }}>Reporté</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="date_debut" class="form-label">À partir de</label>
                    <input type="date" class="form-control" id="date_debut" name="date_debut"
                           value="{{ request('date_debut') }}">
                </div>

                <div class="col-md-2">
                    <label for="date_fin" class="form-label">Jusqu'au</label>
                    <input type="date" class="form-control" id="date_fin" name="date_fin"
                           value="{{ request('date_fin') }}">
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Filtrer
                    </button>
                    <a href="{{ route('events.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>Réinitialiser
                    </a>
                    <div class="float-end">
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="view" id="timeline-view" value="timeline"
                                   {{ request('view', 'timeline') === 'timeline' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary" for="timeline-view">
                                <i class="fas fa-stream"></i> Timeline
                            </label>

                            <input type="radio" class="btn-check" name="view" id="card-view" value="cards"
                                   {{ request('view') === 'cards' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary" for="card-view">
                                <i class="fas fa-th-large"></i> Cartes
                            </label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Vue timeline (par défaut) -->
    @if(request('view', 'timeline') === 'timeline')
        <div class="row">
            <div class="col-12">
                @forelse($events ?? [] as $date => $dateEvents)
                    <div class="mb-4">
                        <!-- En-tête de date -->
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary text-white rounded-circle p-2 me-3">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">
                                    {{ Carbon\Carbon::parse($date)->translatedFormat('l j F Y') }}
                                </h5>
                                <small class="text-muted">{{ count($dateEvents) }} événement(s)</small>
                            </div>
                        </div>

                        <!-- Événements de la journée -->
                        <div class="timeline-container">
                            @foreach($dateEvents as $event)
                                <div class="timeline-item card border-0 shadow-sm mb-3">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-2 text-center">
                                                <div class="time-badge bg-{{ $event->type === 'reunion' ? 'primary' : ($event->type === 'intervention' ? 'warning' : ($event->type === 'formation' ? 'success' : 'info')) }} text-white rounded p-2">
                                                    <div class="fw-bold">{{ Carbon\Carbon::parse($event->date_debut)->format('H:i') }}</div>
                                                    <div class="small">{{ Carbon\Carbon::parse($event->date_fin)->format('H:i') }}</div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="d-flex align-items-start">
                                                    <div class="rounded-circle bg-{{ $event->type === 'reunion' ? 'primary' : ($event->type === 'intervention' ? 'warning' : ($event->type === 'formation' ? 'success' : 'info')) }} bg-opacity-10 p-2 me-3">
                                                        <i class="fas fa-{{ $event->type === 'reunion' ? 'users' : ($event->type === 'intervention' ? 'tools' : ($event->type === 'formation' ? 'chalkboard-teacher' : 'eye')) }} text-{{ $event->type === 'reunion' ? 'primary' : ($event->type === 'intervention' ? 'warning' : ($event->type === 'formation' ? 'success' : 'info')) }}"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1">{{ $event->titre }}</h6>
                                                        <p class="text-muted mb-1 small">{{ Str::limit($event->description, 80) }}</p>
                                                        <div class="small text-muted">
                                                            <i class="fas fa-map-marker-alt me-1"></i>{{ $event->lieu }}
                                                            <i class="fas fa-user ms-3 me-1"></i>{{ $event->organisateur->prenom ?? '' }} {{ $event->organisateur->nom ?? '' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <span class="badge bg-{{ $event->statut === 'termine' ? 'success' : ($event->statut === 'en_cours' ? 'primary' : ($event->statut === 'annule' ? 'danger' : 'secondary')) }}">
                                                    {{ ucfirst(str_replace('_', ' ', $event->statut)) }}
                                                </span>
                                                @if($event->priorite === 'urgente')
                                                    <span class="badge bg-danger ms-1">Urgent</span>
                                                @endif
                                            </div>

                                            <div class="col-md-2 text-end">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('events.show', $event->id) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if(Auth::user()->role === 'admin' || Auth::user()->id === $event->id_organisateur)
                                                        <a href="{{ route('events.edit', $event->id) }}" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif
                                                    @if(Auth::user()->role === 'admin')
                                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                                onclick="deleteEvent({{ $event->id }})" title="Supprimer">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Participants -->
                                        @if($event->participants && $event->participants->count() > 0)
                                            <div class="row mt-2">
                                                <div class="col-12">
                                                    <div class="d-flex align-items-center">
                                                        <small class="text-muted me-2">Participants :</small>
                                                        <div class="d-flex">
                                                            @foreach($event->participants->take(5) as $participant)
                                                                <div class="rounded-circle bg-secondary bg-opacity-10 p-1 me-1" title="{{ $participant->prenom }} {{ $participant->nom }}">
                                                                    <i class="fas fa-user text-secondary small"></i>
                                                                </div>
                                                            @endforeach
                                                            @if($event->participants->count() > 5)
                                                                <small class="text-muted">+{{ $event->participants->count() - 5 }}</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">Aucun événement trouvé</h4>
                        <p class="text-muted">
                            @if(request()->hasAny(['search', 'type', 'statut', 'date_debut', 'date_fin']))
                                Aucun événement ne correspond à vos critères.
                                <br><a href="{{ route('events.index') }}">Voir tous les événements</a>
                            @else
                                Commencez par planifier votre premier événement.
                            @endif
                        </p>
                        <a href="{{ route('events.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Créer un événement
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    @else
        <!-- Vue en cartes -->
        <div class="row">
            @forelse($events ?? [] as $event)
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-{{ $event->type === 'reunion' ? 'primary' : ($event->type === 'intervention' ? 'warning' : ($event->type === 'formation' ? 'success' : 'info')) }} text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-{{ $event->type === 'reunion' ? 'users' : ($event->type === 'intervention' ? 'tools' : ($event->type === 'formation' ? 'chalkboard-teacher' : 'eye')) }} me-2"></i>
                                    <span class="small">{{ ucfirst($event->type) }}</span>
                                </div>
                                <span class="badge bg-white text-dark">
                                    {{ Carbon\Carbon::parse($event->date_debut)->format('d/m') }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">{{ $event->titre }}</h5>
                            <p class="card-text text-muted">{{ Str::limit($event->description, 100) }}</p>

                            <div class="mb-3">
                                <div class="small mb-1">
                                    <i class="fas fa-clock text-muted me-2"></i>
                                    {{ Carbon\Carbon::parse($event->date_debut)->format('d/m/Y H:i') }} -
                                    {{ Carbon\Carbon::parse($event->date_fin)->format('H:i') }}
                                </div>
                                <div class="small mb-1">
                                    <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                    {{ $event->lieu }}
                                </div>
                                <div class="small">
                                    <i class="fas fa-user text-muted me-2"></i>
                                    {{ $event->organisateur->prenom ?? '' }} {{ $event->organisateur->nom ?? '' }}
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-{{ $event->statut === 'termine' ? 'success' : ($event->statut === 'en_cours' ? 'primary' : ($event->statut === 'annule' ? 'danger' : 'secondary')) }}">
                                    {{ ucfirst(str_replace('_', ' ', $event->statut)) }}
                                </span>
                                <small class="text-muted">
                                    {{ $event->participants ? $event->participants->count() : 0 }} participant(s)
                                </small>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="btn-group w-100" role="group">
                                <a href="{{ route('events.show', $event->id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(Auth::user()->role === 'admin' || Auth::user()->id === $event->id_organisateur)
                                    <a href="{{ route('events.edit', $event->id) }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                                @if(Auth::user()->role === 'admin')
                                    <button type="button" class="btn btn-outline-danger btn-sm"
                                            onclick="deleteEvent({{ $event->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">Aucun événement trouvé</h4>
                        <p class="text-muted">Modifiez vos critères de recherche ou créez un nouvel événement.</p>
                        <a href="{{ route('events.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Créer un événement
                        </a>
                    </div>
                </div>
            @endforelse
        </div>
    @endif

    <!-- Pagination -->
    @if(isset($events) && method_exists($events, 'hasPages') && $events->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $events->appends(request()->query())->links() }}
        </div>
    @endif
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer cet événement ?</p>
                <p class="text-danger small">Cette action supprimera également :</p>
                <ul class="small">
                    <li>Toutes les participations confirmées</li>
                    <li>Les tâches associées à cet événement</li>
                    <li>L'historique complet</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.timeline-container {
    position: relative;
    padding-left: 2rem;
}

.timeline-container::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #007bff, #28a745);
}

.timeline-item {
    position: relative;
    margin-left: 1rem;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -1.5rem;
    top: 50%;
    transform: translateY(-50%);
    width: 10px;
    height: 10px;
    background: #007bff;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 0 0 2px #007bff;
}

.time-badge {
    min-width: 80px;
}

.card:hover {
    transform: translateY(-2px);
    transition: transform 0.2s ease;
}
</style>
@endpush

@push('scripts')
<script>
// Fonction de suppression
function deleteEvent(eventId) {
    const deleteForm = document.getElementById('deleteForm');
    deleteForm.action = `/events/${eventId}`;

    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Soumission automatique du formulaire de filtre lors du changement de vue
document.querySelectorAll('input[name="view"]').forEach(input => {
    input.addEventListener('change', function() {
        this.closest('form').submit();
    });
});

// Recherche en temps réel
let searchTimeout;
document.getElementById('search').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const searchTerm = this.value;

    if (searchTerm.length >= 3 || searchTerm.length === 0) {
        searchTimeout = setTimeout(() => {
            this.closest('form').submit();
        }, 500);
    }
});

// Validation des dates
document.getElementById('date_debut').addEventListener('change', function() {
    const dateFin = document.getElementById('date_fin');
    if (this.value) {
        dateFin.min = this.value;
    }
});

document.getElementById('date_fin').addEventListener('change', function() {
    const dateDebut = document.getElementById('date_debut');
    if (this.value) {
        dateDebut.max = this.value;
    }
});

// Animation de la timeline
function animateTimelineItems() {
    const items = document.querySelectorAll('.timeline-item');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateX(0)';
            }
        });
    });

    items.forEach(item => {
        item.style.opacity = '0';
        item.style.transform = 'translateX(-20px)';
        item.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(item);
    });
}

// Initialiser les animations au chargement
document.addEventListener('DOMContentLoaded', animateTimelineItems);

// Mise à jour en temps réel des événements du jour
function updateTodayEvents() {
    fetch('/api/events/today')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mettre à jour le compteur d'événements du jour
                console.log('Événements du jour mis à jour');
            }
        })
        .catch(error => console.log('Erreur refresh events:', error));
}

// Actualiser toutes les 10 minutes
setInterval(updateTodayEvents, 600000);
</script>
@endpush
@endsection
