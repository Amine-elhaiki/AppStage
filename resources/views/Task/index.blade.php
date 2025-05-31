@extends('layouts.app')

@section('title', 'Gestion des tâches - PlanifTech ORMVAT')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-tasks text-primary me-2"></i>
                {{ Auth::user()->isAdmin() ? 'Gestion des tâches' : 'Mes tâches' }}
            </h1>
            <p class="text-muted mb-0">
                {{ Auth::user()->isAdmin() ? 'Vue d\'ensemble de toutes les tâches' : 'Vos tâches assignées et leur progression' }}
            </p>
        </div>
        @can('create', App\Models\Task::class)
            <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>
                Nouvelle tâche
            </a>
        @endcan
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('tasks.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Recherche</label>
                    <input type="text" class="form-control" id="search" name="search"
                           value="{{ request('search') }}" placeholder="Titre, description...">
                </div>

                <div class="col-md-2">
                    <label for="status" class="form-label">Statut</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Tous les statuts</option>
                        <option value="a_faire" {{ request('status') === 'a_faire' ? 'selected' : '' }}>À faire</option>
                        <option value="en_cours" {{ request('status') === 'en_cours' ? 'selected' : '' }}>En cours</option>
                        <option value="termine" {{ request('status') === 'termine' ? 'selected' : '' }}>Terminé</option>
                        <option value="reporte" {{ request('status') === 'reporte' ? 'selected' : '' }}>Reporté</option>
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
                    <label for="project" class="form-label">Projet</label>
                    <select class="form-select" id="project" name="project">
                        <option value="">Tous les projets</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ request('project') == $project->id ? 'selected' : '' }}>
                                {{ $project->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if(Auth::user()->isAdmin() && $users->count() > 0)
                    <div class="col-md-2">
                        <label for="user" class="form-label">Assigné à</label>
                        <select class="form-select" id="user" name="user">
                            <option value="">Tous les utilisateurs</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user') == $user->id ? 'selected' : '' }}>
                                    {{ $user->prenom }} {{ $user->nom }}
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
            <div class="card border-primary">
                <div class="card-body text-center">
                    <i class="fas fa-list-ul fa-2x text-primary mb-2"></i>
                    <h4 class="mb-0">{{ $tasks->total() }}</h4>
                    <small class="text-muted">Total</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                    <h4 class="mb-0">{{ $tasks->where('statut', 'a_faire')->count() }}</h4>
                    <small class="text-muted">À faire</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="fas fa-spinner fa-2x text-info mb-2"></i>
                    <h4 class="mb-0">{{ $tasks->where('statut', 'en_cours')->count() }}</h4>
                    <small class="text-muted">En cours</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <h4 class="mb-0">{{ $tasks->where('statut', 'termine')->count() }}</h4>
                    <small class="text-muted">Terminées</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des tâches -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Liste des tâches</h5>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-sort me-1"></i>Trier par
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'date_echeance', 'direction' => 'asc']) }}">
                            <i class="fas fa-calendar me-2"></i>Date d'échéance
                        </a></li>
                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'priorite', 'direction' => 'desc']) }}">
                            <i class="fas fa-exclamation me-2"></i>Priorité
                        </a></li>
                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'titre', 'direction' => 'asc']) }}">
                            <i class="fas fa-sort-alpha-down me-2"></i>Titre
                        </a></li>
                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => 'desc']) }}">
                            <i class="fas fa-clock me-2"></i>Date de création
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            @forelse($tasks as $task)
                <div class="task-item border-bottom p-3 {{ $task->isOverdue() ? 'bg-danger bg-opacity-10' : '' }}" data-task-id="{{ $task->id }}">
                    <div class="row align-items-center">
                        <div class="col-md-1">
                            <div class="form-check">
                                <input class="form-check-input task-checkbox" type="checkbox"
                                       id="task-{{ $task->id }}"
                                       data-task-id="{{ $task->id }}"
                                       {{ $task->statut === 'termine' ? 'checked' : '' }}>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    @php
                                        $priorityIcon = match($task->priorite) {
                                            'urgente' => 'fas fa-exclamation-triangle text-danger',
                                            'haute' => 'fas fa-exclamation text-warning',
                                            'normale' => 'fas fa-minus text-info',
                                            'basse' => 'fas fa-angle-down text-success',
                                            default => 'fas fa-minus text-secondary'
                                        };
                                    @endphp
                                    <i class="{{ $priorityIcon }}"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 {{ $task->statut === 'termine' ? 'text-decoration-line-through text-muted' : '' }}">
                                        <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none">
                                            {{ $task->titre }}
                                        </a>
                                    </h6>
                                    <small class="text-muted">{{ Str::limit($task->description, 80) }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2">
                            @if($task->project)
                                <span class="badge bg-light text-dark">
                                    <i class="fas fa-project-diagram me-1"></i>
                                    {{ Str::limit($task->project->nom, 20) }}
                                </span>
                            @else
                                <span class="text-muted small">Aucun projet</span>
                            @endif
                        </div>

                        <div class="col-md-2">
                            <div class="d-flex align-items-center">
                                <div class="me-2">
                                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center"
                                         style="width: 30px; height: 30px; font-size: 12px;">
                                        {{ $task->user->initials }}
                                    </div>
                                </div>
                                <div>
                                    <small class="fw-semibold">{{ $task->user->prenom }}</small>
                                    <br>
                                    <small class="text-muted">{{ $task->user->nom }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2">
                            @if($task->date_echeance)
                                <div class="text-center">
                                    <div class="badge bg-{{ $task->isOverdue() ? 'danger' : ($task->isDueToday() ? 'warning' : 'secondary') }}">
                                        {{ $task->date_echeance->format('d/m/Y') }}
                                    </div>
                                    @if($task->isOverdue())
                                        <br><small class="text-danger">En retard</small>
                                    @elseif($task->isDueToday())
                                        <br><small class="text-warning">Aujourd'hui</small>
                                    @endif
                                </div>
                            @else
                                <small class="text-muted">Pas d'échéance</small>
                            @endif
                        </div>

                        <div class="col-md-1">
                            <div class="text-center">
                                <span class="badge bg-{{ $task->status_color }}">
                                    {{ $task->status_label }}
                                </span>
                                <div class="progress mt-1" style="height: 4px;">
                                    <div class="progress-bar bg-{{ $task->status_color }}"
                                         style="width: {{ $task->progression }}%"></div>
                                </div>
                                <small class="text-muted">{{ $task->progression }}%</small>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    @can('update', $task)
                        <div class="row mt-2">
                            <div class="col-12">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('tasks.show', $task) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($task->statut !== 'en_cours')
                                        <button type="button" class="btn btn-outline-info task-action"
                                                data-action="en_cours" data-task-id="{{ $task->id }}">
                                            <i class="fas fa-play"></i>
                                        </button>
                                    @endif
                                    @if($task->statut !== 'termine')
                                        <button type="button" class="btn btn-outline-success task-action"
                                                data-action="termine" data-task-id="{{ $task->id }}">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endif
                                    @can('delete', $task)
                                        <button type="button" class="btn btn-outline-danger"
                                                onclick="confirmDelete({{ $task->id }}, '{{ $task->titre }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @endcan
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="fas fa-tasks fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucune tâche trouvée</h5>
                    <p class="text-muted">
                        @if(request()->hasAny(['search', 'status', 'priority', 'project', 'user']))
                            Aucune tâche ne correspond à vos critères de recherche.
                        @else
                            Vous n'avez aucune tâche assignée pour le moment.
                        @endif
                    </p>
                    @if(!request()->hasAny(['search', 'status', 'priority', 'project', 'user']))
                        @can('create', App\Models\Task::class)
                            <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>
                                Créer une nouvelle tâche
                            </a>
                        @endcan
                    @endif
                </div>
            @endforelse
        </div>

        @if($tasks->hasPages())
            <div class="card-footer">
                {{ $tasks->links() }}
            </div>
        @endif
    </div>
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
                <p>Êtes-vous sûr de vouloir supprimer la tâche "<span id="taskTitle"></span>" ?</p>
                <p class="text-danger"><small>Cette action est irréversible.</small></p>
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
// Gestion des actions sur les tâches
document.addEventListener('DOMContentLoaded', function() {
    // Checkboxes des tâches
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('task-checkbox')) {
            const taskId = parseInt(e.target.dataset.taskId);
            const completed = e.target.checked;
            updateTaskStatus(taskId, completed ? 'termine' : 'a_faire');
        }
    });

    // Actions des boutons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('task-action') || e.target.closest('.task-action')) {
            e.preventDefault();
            const target = e.target.classList.contains('task-action') ? e.target : e.target.closest('.task-action');
            const taskId = parseInt(target.dataset.taskId);
            const action = target.dataset.action;

            if (taskId && action) {
                updateTaskStatus(taskId, action);
            }
        }
    });
});

function updateTaskStatus(taskId, status) {
    fetch(`/api/tasks/${taskId}/quick-update`, {
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
            showToast(data.message || 'Tâche mise à jour avec succès', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Erreur lors de la mise à jour', 'danger');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showToast('Erreur de communication avec le serveur', 'danger');
    });
}

function confirmDelete(taskId, taskTitle) {
    document.getElementById('taskTitle').textContent = taskTitle;
    document.getElementById('deleteForm').action = `/tasks/${taskId}`;
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
document.querySelectorAll('#status, #priority, #project, #user').forEach(select => {
    select.addEventListener('change', function() {
        this.form.submit();
    });
});
</script>
@endpush

@push('styles')
<style>
.task-item {
    transition: all 0.2s ease;
}

.task-item:hover {
    background-color: #f8f9fa !important;
}

.progress {
    border-radius: 10px;
}

.progress-bar {
    border-radius: 10px;
}

.task-checkbox {
    transform: scale(1.2);
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}

.badge {
    font-size: 0.75em;
}
</style>
@endpush
@endsection
