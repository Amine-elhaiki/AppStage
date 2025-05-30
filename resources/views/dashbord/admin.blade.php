@extends('layouts.app')

@section('title', 'Tableau de bord Administrateur')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-tachometer-alt text-primary me-2"></i>
                Tableau de bord Administrateur
            </h1>
            <p class="text-muted mb-0">Vue d'ensemble de l'activité ORMVAT</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt"></i> Actualiser
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#quickActionModal">
                <i class="fas fa-plus"></i> Actions rapides
            </button>
        </div>
    </div>

    <!-- Statistiques générales -->
    <div class="row mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
            <div class="card stats-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-title text-muted mb-1">Utilisateurs actifs</h6>
                            <h2 class="mb-0 text-primary">{{ $stats['total_users'] ?? 0 }}</h2>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-users fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
            <div class="card stats-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-title text-muted mb-1">Projets actifs</h6>
                            <h2 class="mb-0 text-success">{{ $stats['active_projects'] ?? 0 }}</h2>
                            <small class="text-muted">sur {{ $stats['total_projects'] ?? 0 }} total</small>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-project-diagram fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
            <div class="card stats-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-title text-muted mb-1">Tâches en cours</h6>
                            <h2 class="mb-0 text-warning">{{ $stats['in_progress_tasks'] ?? 0 }}</h2>
                            @if(isset($stats['overdue_tasks']) && $stats['overdue_tasks'] > 0)
                                <small class="text-danger">{{ $stats['overdue_tasks'] }} en retard</small>
                            @endif
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-tasks fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
            <div class="card stats-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-title text-muted mb-1">Événements aujourd'hui</h6>
                            <h2 class="mb-0 text-info">{{ $stats['today_events'] ?? 0 }}</h2>
                            <small class="text-muted">{{ $stats['week_reports'] ?? 0 }} rapports cette semaine</small>
                        </div>
                        <div class="text-info">
                            <i class="fas fa-calendar fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Tâches prioritaires -->
        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Tâches prioritaires
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tâche</th>
                                    <th>Assigné à</th>
                                    <th>Échéance</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($priority_tasks ?? [] as $task)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <h6 class="mb-0">{{ Str::limit($task->titre, 30) }}</h6>
                                                @if($task->project)
                                                    <small class="text-muted">{{ $task->project->nom }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 12px;">
                                                {{ $task->user->initials }}
                                            </div>
                                            <span>{{ $task->user->nom }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $task->isOverdue() ? 'danger' : ($task->isDueToday() ? 'warning' : 'secondary') }}">
                                            {{ $task->date_echeance->format('d/m/Y') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-status-{{ $task->statut }}">
                                            {{ $task->status_label }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                                        <br>Aucune tâche prioritaire en attente
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ route('tasks.index', ['priority' => 'haute']) }}" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-eye me-1"></i>
                        Voir toutes les tâches prioritaires
                    </a>
                </div>
            </div>
        </div>

        <!-- Événements du jour -->
        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-day me-2"></i>
                        Événements d'aujourd'hui
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($today_events ?? [] as $event)
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $event->titre }}</h6>
                                    <p class="mb-1 text-muted">{{ Str::limit($event->description, 50) }}</p>
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        {{ $event->lieu }}
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-{{ $event->type_color }}">
                                        {{ $event->type_label }}
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        {{ $event->date_debut->format('H:i') }} - {{ $event->date_fin->format('H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-calendar-times fa-2x mb-2"></i>
                            <br>Aucun événement prévu aujourd'hui
                        </div>
                        @endforelse
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ route('events.calendar') }}" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-calendar me-1"></i>
                        Voir le calendrier complet
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Projets actifs -->
        <div class="col-xl-8 col-lg-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-project-diagram me-2"></i>
                        Projets en cours
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($active_projects ?? [] as $project)
                    <div class="mb-3 @if(!$loop->last) border-bottom pb-3 @endif">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">{{ $project->nom }}</h6>
                            <span class="badge bg-{{ $project->status_color }}">
                                {{ $project->status_label }}
                            </span>
                        </div>
                        <p class="text-muted mb-2">{{ Str::limit($project->description, 100) }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i>
                                    {{ $project->responsable->nom ?? 'Non assigné' }}
                                </small>
                            </div>
                            <div class="text-end">
                                @php
                                    $progress = $project->tasks_count > 0 ? round(($project->completed_tasks_count / $project->tasks_count) * 100) : 0;
                                @endphp
                                <small class="text-muted">{{ $progress }}% ({{ $project->completed_tasks_count }}/{{ $project->tasks_count }})</small>
                                <div class="progress progress-sm mt-1" style="width: 100px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progress }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-folder-open fa-2x mb-2"></i>
                        <br>Aucun projet en cours
                    </div>
                    @endforelse
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-eye me-1"></i>
                        Voir tous les projets
                    </a>
                </div>
            </div>
        </div>

        <!-- Utilisateurs actifs et rapports récents -->
        <div class="col-xl-4 col-lg-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>
                        Équipe active
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($active_users ?? [] as $user)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px; font-size: 14px;">
                                    {{ $user->initials }}
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $user->nom }} {{ $user->prenom }}</h6>
                                    <small class="text-muted">{{ ucfirst($user->role) }}</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary">{{ $user->assigned_tasks_count ?? 0 }}</span>
                                <br>
                                <small class="text-muted">tâches</small>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-user-slash fa-2x mb-2"></i>
                            <br>Aucun utilisateur actif
                        </div>
                        @endforelse
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-eye me-1"></i>
                        Gérer les utilisateurs
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Actions rapides -->
<div class="modal fade" id="quickActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-bolt me-2"></i>
                    Actions rapides
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-6">
                        <a href="{{ route('tasks.create') }}" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center">
                            <i class="fas fa-plus fa-2x mb-2"></i>
                            <span>Nouvelle tâche</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('projects.create') }}" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center">
                            <i class="fas fa-project-diagram fa-2x mb-2"></i>
                            <span>Nouveau projet</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('events.create') }}" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center">
                            <i class="fas fa-calendar-plus fa-2x mb-2"></i>
                            <span>Nouvel événement</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('users.create') }}" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center">
                            <i class="fas fa-user-plus fa-2x mb-2"></i>
                            <span>Nouvel utilisateur</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function refreshDashboard() {
    const refreshBtn = document.querySelector('button[onclick="refreshDashboard()"]');
    const originalContent = refreshBtn.innerHTML;

    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualisation...';
    refreshBtn.disabled = true;

    // Simuler le rechargement des données
    setTimeout(() => {
        location.reload();
    }, 1000);
}

// Actualisation automatique toutes les 5 minutes
setInterval(() => {
    fetch('{{ route("api.dashboard.data") }}')
        .then(response => response.json())
        .then(data => {
            // Mise à jour des statistiques sans recharger la page
            console.log('Données actualisées', data);
        })
        .catch(error => console.error('Erreur lors de l\'actualisation:', error));
}, 300000); // 5 minutes
</script>
@endpush
