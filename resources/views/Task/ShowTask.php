@extends('layouts.app')

@section('title', $task->titre . ' - PlanifTech ORMVAT')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <div class="d-flex align-items-center mb-2">
                <h1 class="h3 mb-0 me-3">{{ $task->titre }}</h1>
                <span class="badge bg-{{ $task->status_color }} fs-6">
                    {{ $task->status_label }}
                </span>
                <span class="badge bg-{{ $task->priority_color }} ms-2">
                    {{ $task->priority_label }}
                </span>
            </div>
            <p class="text-muted mb-0">
                Créée le {{ $task->date_creation->format('d/m/Y à H:i') }} par {{ $task->creator->prenom }} {{ $task->creator->nom }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                Retour à la liste
            </a>
            @can('update', $task)
                <a href="{{ route('tasks.edit', $task) }}" class="btn btn-primary">
                    <i class="fas fa-edit me-2"></i>
                    Modifier
                </a>
            @endcan
        </div>
    </div>

    <div class="row">
        <!-- Contenu principal -->
        <div class="col-lg-8">
            <!-- Informations principales -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Détails de la tâche
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-semibold mb-2">Description</h6>
                            <p class="text-muted">{{ $task->description }}</p>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-6">
                                    <h6 class="fw-semibold mb-2">Assigné à</h6>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center me-2"
                                             style="width: 40px; height: 40px;">
                                            {{ $task->user->initials }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $task->user->prenom }} {{ $task->user->nom }}</div>
                                            <small class="text-muted">{{ ucfirst($task->user->role) }}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <h6 class="fw-semibold mb-2">Progression</h6>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 10px;">
                                            <div class="progress-bar bg-{{ $task->status_color }}"
                                                 style="width: {{ $task->progression }}%"></div>
                                        </div>
                                        <span class="fw-semibold">{{ $task->progression }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($task->project)
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h6 class="fw-semibold mb-2">Projet associé</h6>
                                <div class="card bg-light">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">{{ $task->project->nom }}</h6>
                                                <small class="text-muted">{{ $task->project->zone_geographique }}</small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-{{ $task->project->status_color }}">
                                                    {{ $task->project->status_label }}
                                                </span>
                                                <div class="small text-muted mt-1">
                                                    {{ $task->project->pourcentage_avancement }}% terminé
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($task->commentaires)
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h6 class="fw-semibold mb-2">Commentaires</h6>
                                <div class="alert alert-info">
                                    <i class="fas fa-comment me-2"></i>
                                    {{ $task->commentaires }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions rapides -->
            @can('update', $task)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-bolt me-2"></i>
                            Actions rapides
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @if($task->statut === 'a_faire')
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-info w-100 task-action"
                                            data-action="en_cours" data-task-id="{{ $task->id }}">
                                        <i class="fas fa-play me-2"></i>
                                        Commencer la tâche
                                    </button>
                                </div>
                            @endif

                            @if($task->statut === 'en_cours')
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-success w-100 task-action"
                                            data-action="termine" data-task-id="{{ $task->id }}">
                                        <i class="fas fa-check me-2"></i>
                                        Marquer comme terminée
                                    </button>
                                </div>
                            @endif

                            @if($task->statut !== 'termine')
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="progressInput"
                                               min="0" max="100" value="{{ $task->progression }}"
                                               placeholder="Progression %">
                                        <button type="button" class="btn btn-outline-primary"
                                                onclick="updateProgress()">
                                            <i class="fas fa-percentage"></i>
                                        </button>
                                    </div>
                                </div>
                            @endif

                            @if($task->statut === 'termine')
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-warning w-100 task-action"
                                            data-action="en_cours" data-task-id="{{ $task->id }}">
                                        <i class="fas fa-undo me-2"></i>
                                        Rouvrir la tâche
                                    </button>
                                </div>
                            @endif

                            <div class="col-md-4">
                                <a href="{{ route('tasks.duplicate', $task) }}" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-copy me-2"></i>
                                    Dupliquer
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endcan

            <!-- Historique des modifications -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>
                        Historique
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Tâche créée</h6>
                                <p class="text-muted mb-1">
                                    Créée par {{ $task->creator->prenom }} {{ $task->creator->nom }}
                                </p>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $task->date_creation->format('d/m/Y à H:i') }}
                                </small>
                            </div>
                        </div>

                        @if($task->date_debut_reelle)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Tâche commencée</h6>
                                    <p class="text-muted mb-1">
                                        Démarrée par {{ $task->user->prenom }} {{ $task->user->nom }}
                                    </p>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        {{ $task->date_debut_reelle->format('d/m/Y à H:i') }}
                                    </small>
                                </div>
                            </div>
                        @endif

                        @if($task->date_fin_reelle)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Tâche terminée</h6>
                                    <p class="text-muted mb-1">
                                        Complétée par {{ $task->user->prenom }} {{ $task->user->nom }}
                                    </p>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        {{ $task->date_fin_reelle->format('d/m/Y à H:i') }}
                                    </small>
                                    @if($task->getDurationInDays())
                                        <small class="text-success d-block">
                                            <i class="fas fa-clock me-1"></i>
                                            Durée: {{ $task->getDurationInDays() }} jour(s)
                                        </small>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Informations complémentaires -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-calendar me-2"></i>
                        Planning
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="small fw-semibold text-muted">DATE DE CRÉATION</label>
                        <div>{{ $task->date_creation->format('d/m/Y à H:i') }}</div>
                    </div>

                    @if($task->date_echeance)
                        <div class="mb-3">
                            <label class="small fw-semibold text-muted">DATE D'ÉCHÉANCE</label>
                            <div class="d-flex align-items-center">
                                <span class="me-2">{{ $task->date_echeance->format('d/m/Y') }}</span>
                                @if($task->isOverdue())
                                    <span class="badge bg-danger">En retard</span>
                                @elseif($task->isDueToday())
                                    <span class="badge bg-warning">Aujourd'hui</span>
                                @elseif($task->isDueSoon())
                                    <span class="badge bg-info">Bientôt</span>
                                @endif
                            </div>
                            @if($task->date_echeance->isFuture() && $task->statut !== 'termine')
                                <small class="text-muted">
                                    Dans {{ $task->date_echeance->diffForHumans() }}
                                </small>
                            @endif
                        </div>
                    @endif

                    @if($task->date_debut_reelle)
                        <div class="mb-3">
                            <label class="small fw-semibold text-muted">DÉBUT RÉEL</label>
                            <div>{{ $task->date_debut_reelle->format('d/m/Y à H:i') }}</div>
                        </div>
                    @endif

                    @if($task->date_fin_reelle)
                        <div class="mb-3">
                            <label class="small fw-semibold text-muted">FIN RÉELLE</label>
                            <div>{{ $task->date_fin_reelle->format('d/m/Y à H:i') }}</div>
                        </div>
                    @endif

                    <div>
                        <label class="small fw-semibold text-muted">DERNIÈRE MODIFICATION</label>
                        <div>{{ $task->updated_at->format('d/m/Y à H:i') }}</div>
                        <small class="text-muted">{{ $task->updated_at->diffForHumans() }}</small>
                    </div>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Statistiques
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small">Progression</span>
                            <span class="fw-semibold">{{ $task->progression }}%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-{{ $task->status_color }}"
                                 style="width: {{ $task->progression }}%"></div>
                        </div>
                    </div>

                    @if($task->getEstimatedDuration())
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span class="small">Durée estimée</span>
                                <span class="fw-semibold">{{ $task->getEstimatedDuration() }} jour(s)</span>
                            </div>
                        </div>
                    @endif

                    @if($task->getDurationInDays())
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span class="small">Durée réelle</span>
                                <span class="fw-semibold">{{ $task->getDurationInDays() }} jour(s)</span>
                            </div>
                        </div>
                    @endif

                    <div>
                        <div class="d-flex justify-content-between">
                            <span class="small">Priorité</span>
                            <span class="badge bg-{{ $task->priority_color }}">{{ $task->priority_label }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions d'administration -->
            @can('delete', $task)
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Zone de danger
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">
                            La suppression d'une tâche est irréversible. Toutes les données associées seront perdues.
                        </p>
                        <button type="button" class="btn btn-outline-danger w-100"
                                onclick="confirmDelete()">
                            <i class="fas fa-trash me-2"></i>
                            Supprimer la tâche
                        </button>
                    </div>
                </div>
            @endcan
        </div>
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
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Attention !</strong> Cette action est irréversible.
                </div>
                <p>Êtes-vous sûr de vouloir supprimer la tâche "<strong>{{ $task->titre }}</strong>" ?</p>
                <ul class="text-muted small">
                    <li>Toutes les informations de la tâche seront perdues</li>
                    <li>L'historique sera supprimé</li>
                    <li>Cette action ne peut pas être annulée</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form action="{{ route('tasks.destroy', $task) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>
                        Supprimer définitivement
                    </button>
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
// Actions sur la tâche
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

function updateProgress() {
    const progressInput = document.getElementById('progressInput');
    const progression = parseInt(progressInput.value);

    if (progression < 0 || progression > 100) {
        showToast('La progression doit être entre 0 et 100%', 'warning');
        return;
    }

    fetch(`/api/tasks/{{ $task->id }}/quick-update`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({ progression: progression })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Progression mise à jour avec succès', 'success');
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

function confirmDelete() {
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    const toastId = 'toast-' + Date.now();
    const bgClass = type === 'success' ? 'bg-success' : type === 'danger' ? 'bg-danger' : type === 'warning' ? 'bg-warning' : 'bg-info';

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

// Raccourcis clavier
document.addEventListener('keydown', function(e) {
    @can('update', $task)
        // E pour éditer
        if (e.key === 'e' && !e.target.matches('input, textarea')) {
            window.location.href = "{{ route('tasks.edit', $task) }}";
        }

        // Espace pour changer le statut
        if (e.key === ' ' && !e.target.matches('input, textarea')) {
            e.preventDefault();
            @if($task->statut === 'a_faire')
                updateTaskStatus({{ $task->id }}, 'en_cours');
            @elseif($task->statut === 'en_cours')
                updateTaskStatus({{ $task->id }}, 'termine');
            @endif
        }
    @endcan

    // Échap pour retourner à la liste
    if (e.key === 'Escape') {
        window.location.href = "{{ route('tasks.index') }}";
    }
});
</script>
@endpush

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -22px;
    top: 15px;
    bottom: -20px;
    width: 2px;
    background-color: #dee2e6;
}

.timeline-marker {
    position: absolute;
    left: -28px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #dee2e6;
}

.progress {
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar {
    border-radius: 10px;
    transition: width 0.3s ease;
}

.card {
    border-radius: 12px;
    border: none;
}

.badge {
    font-size: 0.75em;
}

.btn {
    border-radius: 8px;
}
</style>
@endpush
@endsection
