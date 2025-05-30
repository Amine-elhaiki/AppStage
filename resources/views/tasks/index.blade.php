@extends('layouts.app')

@section('title', 'Gestion des tâches')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-tasks text-primary me-2"></i>
                Gestion des tâches
            </h1>
            <p class="text-muted mb-0">
                Gérez et suivez toutes les tâches d'intervention technique
            </p>
        </div>
        <div class="d-flex gap-2">
            @can('create', App\Models\Task::class)
            <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                Nouvelle tâche
            </a>
            @endcan
            <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="fas fa-filter me-1"></i>
                Filtres
            </button>
            <a href="{{ route('tasks.export', request()->query()) }}" class="btn btn-outline-success">
                <i class="fas fa-download me-1"></i>
                Exporter
            </a>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <i class="fas fa-clipboard-list fa-2x text-primary mb-2"></i>
                    <h4 class="mb-0">{{ $tasks->total() }}</h4>
                    <small class="text-muted">Total tâches</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x text-warning mb-2"></i>
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
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                    <h4 class="mb-0">{{ $tasks->filter(function($task) { return $task->isOverdue(); })->count() }}</h4>
                    <small class="text-muted">En retard</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres actifs -->
    @if(request()->hasAny(['statut', 'priorite', 'utilisateur', 'projet', 'search']))
    <div class="alert alert-info d-flex align-items-center justify-content-between">
        <div>
            <i class="fas fa-filter me-2"></i>
            <strong>Filtres actifs :</strong>
            @if(request('statut'))
                <span class="badge bg-primary ms-2">Status: {{ ucfirst(str_replace('_', ' ', request('statut'))) }}</span>
            @endif
            @if(request('priorite'))
                <span class="badge bg-warning ms-2">Priorité: {{ ucfirst(request('priorite')) }}</span>
            @endif
            @if(request('utilisateur'))
                <span class="badge bg-info ms-2">Utilisateur: {{ $users->find(request('utilisateur'))->full_name ?? 'Inconnu' }}</span>
            @endif
            @if(request('projet'))
                <span class="badge bg-success ms-2">Projet: {{ $projects->find(request('projet'))->nom ?? 'Inconnu' }}</span>
            @endif
            @if(request('search'))
                <span class="badge bg-secondary ms-2">Recherche: "{{ request('search') }}"</span>
            @endif
        </div>
        <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-times me-1"></i>
            Effacer les filtres
        </a>
    </div>
    @endif

    <!-- Barre de recherche rapide -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('tasks.index') }}" class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" name="search" placeholder="Rechercher une tâche..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="statut">
                        <option value="">Tous les statuts</option>
                        <option value="a_faire" {{ request('statut') === 'a_faire' ? 'selected' : '' }}>À faire</option>
                        <option value="en_cours" {{ request('statut') === 'en_cours' ? 'selected' : '' }}>En cours</option>
                        <option value="termine" {{ request('statut') === 'termine' ? 'selected' : '' }}>Terminé</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="priorite">
                        <option value="">Toutes les priorités</option>
                        <option value="haute" {{ request('priorite') === 'haute' ? 'selected' : '' }}>Haute</option>
                        <option value="moyenne" {{ request('priorite') === 'moyenne' ? 'selected' : '' }}>Moyenne</option>
                        <option value="basse" {{ request('priorite') === 'basse' ? 'selected' : '' }}>Basse</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="sort">
                        <option value="date_echeance" {{ request('sort') === 'date_echeance' ? 'selected' : '' }}>Par échéance</option>
                        <option value="priorite" {{ request('sort') === 'priorite' ? 'selected' : '' }}>Par priorité</option>
                        <option value="date_creation" {{ request('sort') === 'date_creation' ? 'selected' : '' }}>Par création</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>
                        Rechercher
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des tâches -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if($tasks->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tâche</th>
                            <th>Assigné à</th>
                            <th>Projet</th>
                            <th>Priorité</th>
                            <th>Status</th>
                            <th>Progression</th>
                            <th>Échéance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $task)
                        <tr class="@if($task->isOverdue()) table-danger @elseif($task->isDueToday()) table-warning @endif">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">{{ $task->titre }}</h6>
                                        <small class="text-muted">{{ Str::limit($task->description, 50) }}</small>
                                        @if($task->isOverdue())
                                            <br><small class="text-danger">
                                                <i class="fas fa-clock me-1"></i>
                                                En retard de {{ abs($task->days_remaining) }} jour(s)
                                            </small>
                                        @elseif($task->isDueToday())
                                            <br><small class="text-warning">
                                                <i class="fas fa-exclamation-circle me-1"></i>
                                                Échéance aujourd'hui
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 12px;">
                                        {{ $task->user->initials }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $task->user->nom }}</div>
                                        <small class="text-muted">{{ $task->user->prenom }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($task->project)
                                    <span class="badge bg-success">
                                        <i class="fas fa-project-diagram me-1"></i>
                                        {{ Str::limit($task->project->nom, 20) }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-priority-{{ $task->priorite }}">
                                    @if($task->priorite === 'haute')
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                    @elseif($task->priorite === 'moyenne')
                                        <i class="fas fa-minus me-1"></i>
                                    @else
                                        <i class="fas fa-arrow-down me-1"></i>
                                    @endif
                                    {{ $task->priority_label }}
                                </span>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm badge bg-status-{{ $task->statut }} dropdown-toggle border-0" type="button" data-bs-toggle="dropdown" {{ auth()->user()->cannot('update', $task) ? 'disabled' : '' }}>
                                        {{ $task->status_label }}
                                    </button>
                                    @can('update', $task)
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="updateTaskStatus({{ $task->id }}, 'a_faire')">
                                            <i class="fas fa-pause me-2"></i>À faire
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="updateTaskStatus({{ $task->id }}, 'en_cours')">
                                            <i class="fas fa-play me-2"></i>En cours
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="updateTaskStatus({{ $task->id }}, 'termine')">
                                            <i class="fas fa-check me-2"></i>Terminé
                                        </a></li>
                                    </ul>
                                    @endcan
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                        <div class="progress-bar bg-{{ $task->status_color }}" role="progressbar" style="width: {{ $task->progression }}%">
                                            {{ $task->progression }}%
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-center">
                                    <div class="fw-semibold">{{ $task->date_echeance->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $task->time_remaining }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @can('view', $task)
                                        <li><a class="dropdown-item" href="{{ route('tasks.show', $task) }}">
                                            <i class="fas fa-eye me-2"></i>Voir détails
                                        </a></li>
                                        @endcan
                                        @can('update', $task)
                                        <li><a class="dropdown-item" href="{{ route('tasks.edit', $task) }}">
                                            <i class="fas fa-edit me-2"></i>Modifier
                                        </a></li>
                                        @endcan
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="{{ route('reports.create', ['task_id' => $task->id]) }}">
                                            <i class="fas fa-file-alt me-2"></i>Créer un rapport
                                        </a></li>
                                        @can('delete', $task)
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteTask({{ $task->id }})">
                                            <i class="fas fa-trash me-2"></i>Supprimer
                                        </a></li>
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Aucune tâche trouvée</h5>
                <p class="text-muted mb-4">
                    @if(request()->hasAny(['statut', 'priorite', 'utilisateur', 'projet', 'search']))
                        Aucune tâche ne correspond à vos critères de recherche.
                    @else
                        Commencez par créer votre première tâche.
                    @endif
                </p>
                @can('create', App\Models\Task::class)
                <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Créer une nouvelle tâche
                </a>
                @endcan
            </div>
            @endif
        </div>

        @if($tasks->hasPages())
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Affichage de {{ $tasks->firstItem() }} à {{ $tasks->lastItem() }} sur {{ $tasks->total() }} tâches
                </div>
                <div>
                    {{ $tasks->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Modal Filtres avancés -->
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-filter me-2"></i>
                    Filtres avancés
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET" action="{{ route('tasks.index') }}">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="filter_statut" class="form-label">Statut</label>
                            <select class="form-select" id="filter_statut" name="statut">
                                <option value="">Tous les statuts</option>
                                <option value="a_faire" {{ request('statut') === 'a_faire' ? 'selected' : '' }}>À faire</option>
                                <option value="en_cours" {{ request('statut') === 'en_cours' ? 'selected' : '' }}>En cours</option>
                                <option value="termine" {{ request('statut') === 'termine' ? 'selected' : '' }}>Terminé</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="filter_priorite" class="form-label">Priorité</label>
                            <select class="form-select" id="filter_priorite" name="priorite">
                                <option value="">Toutes les priorités</option>
                                <option value="haute" {{ request('priorite') === 'haute' ? 'selected' : '' }}>Haute</option>
                                <option value="moyenne" {{ request('priorite') === 'moyenne' ? 'selected' : '' }}>Moyenne</option>
                                <option value="basse" {{ request('priorite') === 'basse' ? 'selected' : '' }}>Basse</option>
                            </select>
                        </div>
                        @if(auth()->user()->isAdmin())
                        <div class="col-md-6">
                            <label for="filter_utilisateur" class="form-label">Assigné à</label>
                            <select class="form-select" id="filter_utilisateur" name="utilisateur">
                                <option value="">Tous les utilisateurs</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('utilisateur') == $user->id ? 'selected' : '' }}>
                                    {{ $user->full_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-md-6">
                            <label for="filter_projet" class="form-label">Projet</label>
                            <select class="form-select" id="filter_projet" name="projet">
                                <option value="">Tous les projets</option>
                                @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ request('projet') == $project->id ? 'selected' : '' }}>
                                    {{ $project->nom }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="filter_date_debut" class="form-label">Date début</label>
                            <input type="date" class="form-control" id="filter_date_debut" name="date_debut" value="{{ request('date_debut') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="filter_date_fin" class="form-label">Date fin</label>
                            <input type="date" class="form-control" id="filter_date_fin" name="date_fin" value="{{ request('date_fin') }}">
                        </div>
                        <div class="col-12">
                            <label for="filter_search" class="form-label">Recherche dans le titre et la description</label>
                            <input type="text" class="form-control" id="filter_search" name="search" placeholder="Mot-clé..." value="{{ request('search') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                    <button type="submit" class="btn btn-primary">Appliquer les filtres</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateTaskStatus(taskId, status) {
    fetch(`/tasks/${taskId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ statut: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Erreur lors de la mise à jour', 'danger');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showToast('Erreur lors de la mise à jour', 'danger');
    });
}

function deleteTask(taskId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette tâche ?')) {
        fetch(`/tasks/${taskId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Tâche supprimée avec succès', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('Erreur lors de la suppression', 'danger');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showToast('Erreur lors de la suppression', 'danger');
        });
    }
}

// Auto-refresh toutes les 2 minutes
setInterval(() => {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('auto_refresh', '1');

    fetch(currentUrl.toString())
        .then(response => response.text())
        .then(html => {
            // Mise à jour silencieuse des statistiques
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newStats = doc.querySelectorAll('.card.border-primary h4, .card.border-warning h4, .card.border-success h4, .card.border-danger h4');
            const currentStats = document.querySelectorAll('.card.border-primary h4, .card.border-warning h4, .card.border-success h4, .card.border-danger h4');

            newStats.forEach((stat, index) => {
                if (currentStats[index] && currentStats[index].textContent !== stat.textContent) {
                    currentStats[index].textContent = stat.textContent;
                    currentStats[index].classList.add('text-success');
                    setTimeout(() => {
                        currentStats[index].classList.remove('text-success');
                    }, 2000);
                }
            });
        })
        .catch(error => console.log('Erreur de mise à jour:', error));
}, 120000); // 2 minutes
</script>
@endpush
