@extends('layouts.app')

@section('title', 'Gestion des projets - PlanifTech ORMVAT')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-project-diagram text-success me-2"></i>
                {{ Auth::user()->isAdmin() ? 'Gestion des projets' : 'Mes projets' }}
            </h1>
            <p class="text-muted mb-0">
                {{ Auth::user()->isAdmin() ? 'Vue d\'ensemble de tous les projets ORMVAT' : 'Projets auxquels vous participez' }}
            </p>
        </div>
        @can('create', App\Models\Project::class)
            <a href="{{ route('projects.create') }}" class="btn btn-success">
                <i class="fas fa-plus me-2"></i>
                Nouveau projet
            </a>
        @endcan
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('projects.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Recherche</label>
                    <input type="text" class="form-control" id="search" name="search"
                           value="{{ request('search') }}" placeholder="Nom, description, zone...">
                </div>

                <div class="col-md-2">
                    <label for="status" class="form-label">Statut</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Tous les statuts</option>
                        <option value="planifie" {{ request('status') === 'planifie' ? 'selected' : '' }}>Planifié</option>
                        <option value="en_cours" {{ request('status') === 'en_cours' ? 'selected' : '' }}>En cours</option>
                        <option value="suspendu" {{ request('status') === 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                        <option value="termine" {{ request('status') === 'termine' ? 'selected' : '' }}>Terminé</option>
                        <option value="annule" {{ request('status') === 'annule' ? 'selected' : '' }}>Annulé</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="priority" class="form-label">Priorité</label>
                    <select class="form-select" id="priority" name="priority">
                        <option value="">Toutes les priorités</option>
                        <option value="urgente" {{ request('priority') === 'urgente' ? 'selected' : '' }}>Urgente</option>
                        <option value="haute" {{ request('priority') === 'haute' ? 'selected' : '' }}>Haute</option>
                        <option value="normale" {{ request('priority') === 'normale' ? 'selected' : '' }}>Normale</option>
                        <option value="basse" {{ request('priority') === 'basse' ? 'selected' : '' }}>Basse</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="zone" class="form-label">Zone</label>
                    <select class="form-select" id="zone" name="zone">
                        <option value="">Toutes les zones</option>
                        @foreach($zones as $zone)
                            <option value="{{ $zone }}" {{ request('zone') === $zone ? 'selected' : '' }}>
                                {{ $zone }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if(Auth::user()->isAdmin() && $responsables->count() > 0)
                    <div class="col-md-2">
                        <label for="responsable" class="form-label">Responsable</label>
                        <select class="form-select" id="responsable" name="responsable">
                            <option value="">Tous les responsables</option>
                            @foreach($responsables as $responsable)
                                <option value="{{ $responsable->id }}" {{ request('responsable') == $responsable->id ? 'selected' : '' }}>
                                    {{ $responsable->prenom }} {{ $responsable->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="fas fa-project-diagram fa-2x text-success mb-2"></i>
                    <h4 class="mb-0">{{ $projects->total() }}</h4>
                    <small class="text-muted">Total projets</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <i class="fas fa-play fa-2x text-primary mb-2"></i>
                    <h4 class="mb-0">{{ $projects->where('statut', 'en_cours')->count() }}</h4>
                    <small class="text-muted">En cours</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x text-info mb-2"></i>
                    <h4 class="mb-0">{{ $projects->where('statut', 'termine')->count() }}</h4>
                    <small class="text-muted">Terminés</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="fas fa-pause fa-2x text-warning mb-2"></i>
                    <h4 class="mb-0">{{ $projects->where('statut', 'suspendu')->count() }}</h4>
                    <small class="text-muted">Suspendus</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des projets -->
    <div class="row">
        @forelse($projects as $project)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 project-card" data-project-id="{{ $project->id }}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-{{ $project->status_color }}">
                                {{ $project->status_label }}
                            </span>
                            <span class="badge bg-{{ $project->priority_color }} ms-1">
                                {{ ucfirst($project->priorite) }}
                            </span>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('projects.show', $project) }}">
                                    <i class="fas fa-eye me-2"></i>Voir détails
                                </a></li>
                                @can('update', $project)
                                    <li><a class="dropdown-item" href="{{ route('projects.edit', $project) }}">
                                        <i class="fas fa-edit me-2"></i>Modifier
                                    </a></li>
                                @endcan
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('projects.tasks', $project) }}">
                                    <i class="fas fa-tasks me-2"></i>Voir tâches ({{ $project->tasks_count }})
                                </a></li>
                                @can('delete', $project)
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#"
                                           onclick="confirmDelete({{ $project->id }}, '{{ $project->nom }}')">
                                        <i class="fas fa-trash me-2"></i>Supprimer
                                    </a></li>
                                @endcan
                            </ul>
                        </div>
                    </div>

                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="{{ route('projects.show', $project) }}" class="text-decoration-none">
                                {{ $project->nom }}
                            </a>
                        </h5>
                        <p class="card-text text-muted">
                            {{ Str::limit($project->description, 100) }}
                        </p>

                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                {{ $project->zone_geographique }}
                            </small>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-semibold">Progression</span>
                                <span class="small">{{ $project->pourcentage_avancement }}%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                @php
                                    $progress = $project->tasks_count > 0 ?
                                        round(($project->completed_tasks_count / $project->tasks_count) * 100) :
                                        $project->pourcentage_avancement;
                                @endphp
                                <div class="progress-bar bg-{{ $project->status_color }}"
                                     style="width: {{ $progress }}%"></div>
                            </div>
                            <small class="text-muted">
                                {{ $project->completed_tasks_count }}/{{ $project->tasks_count }} tâches terminées
                            </small>
                        </div>

                        @if($project->responsable)
                            <div class="d-flex align-items-center mb-2">
                                <div class="me-2">
                                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center"
                                         style="width: 30px; height: 30px; font-size: 12px;">
                                        {{ $project->responsable->initials }}
                                    </div>
                                </div>
                                <div>
                                    <small class="fw-semibold">{{ $project->responsable->prenom }} {{ $project->responsable->nom }}</small>
                                    <br>
                                    <small class="text-muted">Responsable</small>
                                </div>
                            </div>
                        @endif

                        @if($project->budget)
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-dollar-sign me-1"></i>
                                    Budget: {{ number_format($project->budget, 0, ',', ' ') }} MAD
                                </small>
                            </div>
                        @endif
                    </div>

                    <div class="card-footer bg-transparent">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                @if($project->date_fin)
                                    Échéance: {{ $project->date_fin->format('d/m/Y') }}
                                    @if($project->isOverdue())
                                        <span class="text-danger">(En retard)</span>
                                    @endif
                                @else
                                    Pas d'échéance
                                @endif
                            </small>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('projects.show', $project) }}"
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('update', $project)
                                    @if($project->statut !== 'termine')
                                        <button type="button" class="btn btn-outline-success btn-sm project-action"
                                                data-action="termine" data-project-id="{{ $project->id }}"
                                                title="Marquer comme terminé">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endif
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-project-diagram fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucun projet trouvé</h5>
                    <p class="text-muted">
                        @if(request()->hasAny(['search', 'status', 'priority', 'zone', 'responsable']))
                            Aucun projet ne correspond à vos critères de recherche.
                        @else
                            Aucun projet n'est disponible pour le moment.
                        @endif
                    </p>
                    @if(!request()->hasAny(['search', 'status', 'priority', 'zone', 'responsable']))
                        @can('create', App\Models\Project::class)
                            <a href="{{ route('projects.create') }}" class="btn btn-success">
                                <i class="fas fa-plus me-2"></i>
                                Créer le premier projet
                            </a>
                        @endcan
                    @endif
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($projects->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $projects->links() }}
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
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Attention !</strong> Cette action supprimera également toutes les tâches, événements et rapports associés.
                </div>
                <p>Êtes-vous sûr de vouloir supprimer le projet "<span id="projectTitle"></span>" ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>
@endsection

@push('scripts')
<script>
// Actions sur les projets
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('project-action') || e.target.closest('.project-action')) {
        e.preventDefault();
        const target = e.target.classList.contains('project-action') ? e.target : e.target.closest('.project-action');
        const projectId = parseInt(target.dataset.projectId);
        const action = target.dataset.action;

        if (projectId && action) {
            updateProjectStatus(projectId, action);
        }
    }
});

function updateProjectStatus(projectId, status) {
    fetch(`/api/projects/${projectId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({ statut: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Statut mis à jour avec succès', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Erreur lors de la mise à jour', 'danger');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showToast('Erreur de communication avec le serveur', 'danger');
    });
}

function confirmDelete(projectId, projectTitle) {
    document.getElementById('projectTitle').textContent = projectTitle;
    document.getElementById('deleteForm').action = `/projects/${projectId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    const toastId = 'toast-' + Date.now();
    const bgClass = type === 'success' ? 'bg-success' : type === 'danger' ? 'bg-danger' : 'bg-info';

    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', toastHtml);

    const toast = new bootstrap.Toast(document.getElementById(toastId), { autohide: true, delay: 4000 });
    toast.show();

    document.getElementById(toastId).addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}

// Auto-submit form on filter change
document.querySelectorAll('#status, #priority, #zone, #responsable').forEach(select => {
    select.addEventListener('change', function() {
        this.form.submit();
    });
});

// Animation des cartes au survol
document.querySelectorAll('.project-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-5px)';
        this.style.transition = 'transform 0.2s ease';
        this.style.boxShadow = '0 8px 25px rgba(0,0,0,0.15)';
    });

    card.addEventListener('mouseleave', function() {
        this.style.transform = '';
        this.style.boxShadow = '';
    });
});
</script>
@endpush

@push('styles')
<style>
.project-card {
    transition: all 0.2s ease;
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.project-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}

.progress {
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar {
    border-radius: 10px;
    transition: width 0.3s ease;
}

.badge {
    font-size: 0.7em;
    padding: 0.4em 0.6em;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
}

.dropdown-menu {
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
</style>
@endpush
@endsection
