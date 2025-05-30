@extends('layouts.app')

@section('title', 'Mon tableau de bord')

@section('content')
<div class="container-fluid">
    <!-- En-tête personnalisé -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h3 mb-2">
                                Bonjour {{ auth()->user()->prenom }} !
                                <i class="fas fa-hand-wave text-warning"></i>
                            </h1>
                            <p class="mb-0 opacity-75">
                                Voici un aperçu de vos activités du jour - {{ now()->format('l j F Y') }}
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <button class="btn btn-light btn-sm" id="refreshBtn">
                                    <i class="fas fa-sync-alt"></i> Actualiser
                                </button>
                                <a href="{{ route('reports.create') }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-plus"></i> Nouveau rapport
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mes statistiques -->
    <div class="row mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
            <div class="card stats-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-title text-muted mb-1">Mes tâches</h6>
                            <h2 class="mb-0 text-primary">{{ $stats['my_tasks'] ?? 0 }}</h2>
                            <small class="text-muted">{{ $stats['pending_tasks'] ?? 0 }} en attente</small>
                        </div>
                        <div class="text-primary">
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
                            <h6 class="card-title text-muted mb-1">Terminées</h6>
                            <h2 class="mb-0 text-success">{{ $stats['completed_tasks'] ?? 0 }}</h2>
                            @php
                                $total = $stats['my_tasks'] ?? 0;
                                $completed = $stats['completed_tasks'] ?? 0;
                                $rate = $total > 0 ? round(($completed / $total) * 100) : 0;
                            @endphp
                            <small class="text-success">{{ $rate }}% de réussite</small>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
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
                            <h6 class="card-title text-muted mb-1">En retard</h6>
                            <h2 class="mb-0 text-danger">{{ $stats['overdue_tasks'] ?? 0 }}</h2>
                            @if(($stats['overdue_tasks'] ?? 0) > 0)
                                <small class="text-danger">Action requise</small>
                            @else
                                <small class="text-success">Tout est à jour !</small>
                            @endif
                        </div>
                        <div class="text-danger">
                            <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
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
                            <h2 class="mb-0 text-info">{{ $stats['my_events_today'] ?? 0 }}</h2>
                            <small class="text-muted">{{ $stats['my_reports_week'] ?? 0 }} rapports cette semaine</small>
                        </div>
                        <div class="text-info">
                            <i class="fas fa-calendar-day fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Mes tâches prioritaires -->
        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-star text-warning me-2"></i>
                        Mes tâches prioritaires
                    </h5>
                    <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye me-1"></i>
                        Voir tout
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($my_priority_tasks ?? [] as $task)
                        <div class="list-group-item task-item"
                             data-task-id="{{ $task->id ?? 0 }}"
                             data-task-title="{{ $task->titre ?? 'Tâche' }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <h6 class="mb-0 me-2">{{ $task->titre ?? 'Titre non défini' }}</h6>
                                        @php
                                            $priorityClass = match($task->priorite ?? 'normale') {
                                                'haute' => 'bg-danger',
                                                'moyenne' => 'bg-warning',
                                                'basse' => 'bg-success',
                                                default => 'bg-secondary'
                                            };
                                            $priorityLabel = match($task->priorite ?? 'normale') {
                                                'haute' => 'Haute',
                                                'moyenne' => 'Moyenne',
                                                'basse' => 'Basse',
                                                default => 'Normale'
                                            };
                                        @endphp
                                        <span class="badge {{ $priorityClass }}">{{ $priorityLabel }}</span>
                                    </div>
                                    <p class="mb-1 text-muted small">{{ Str::limit($task->description ?? '', 80) }}</p>
                                    <div class="d-flex align-items-center">
                                        @if(isset($task->project) && $task->project)
                                            <span class="badge bg-light text-dark me-2">
                                                <i class="fas fa-project-diagram me-1"></i>
                                                {{ $task->project->nom ?? 'Projet' }}
                                            </span>
                                        @endif
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            @if(isset($task->date_echeance) && $task->date_echeance)
                                                {{ $task->date_echeance->format('d/m/Y') }}
                                            @else
                                                Non définie
                                            @endif
                                        </small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-cog"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="{{ route('tasks.show', $task->id ?? 1) }}">
                                                <i class="fas fa-eye me-2"></i>Voir détails
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item task-action"
                                                   data-action="en_cours"
                                                   data-task-id="{{ $task->id ?? 0 }}"
                                                   href="#">
                                                <i class="fas fa-play me-2 text-primary"></i>Commencer
                                            </a></li>
                                            <li><a class="dropdown-item task-action"
                                                   data-action="termine"
                                                   data-task-id="{{ $task->id ?? 0 }}"
                                                   href="#">
                                                <i class="fas fa-check me-2 text-success"></i>Terminer
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="progress progress-sm mt-2">
                                @php
                                    $progression = $task->progression ?? 0;
                                    $statusColor = match($task->statut ?? 'a_faire') {
                                        'termine' => 'bg-success',
                                        'en_cours' => 'bg-primary',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <div class="progress-bar {{ $statusColor }}" role="progressbar" style="width: {{ $progression }}%">
                                    {{ $progression }}%
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-thumbs-up fa-2x mb-2 text-success"></i>
                            <br>Aucune tâche prioritaire en attente
                            <br><small>Excellent travail !</small>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Mes événements du jour -->
        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-day text-info me-2"></i>
                        Mes événements du jour
                    </h5>
                    <a href="{{ route('events.calendar') }}" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-calendar me-1"></i>
                        Calendrier
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($my_today_events ?? [] as $event)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <h6 class="mb-0 me-2">{{ $event->titre ?? 'Événement' }}</h6>
                                        @php
                                            $typeClass = match($event->type ?? 'reunion') {
                                                'intervention' => 'bg-danger',
                                                'reunion' => 'bg-primary',
                                                'formation' => 'bg-success',
                                                'visite' => 'bg-info',
                                                default => 'bg-secondary'
                                            };
                                            $typeLabel = match($event->type ?? 'reunion') {
                                                'intervention' => 'Intervention',
                                                'reunion' => 'Réunion',
                                                'formation' => 'Formation',
                                                'visite' => 'Visite',
                                                default => 'Événement'
                                            };
                                        @endphp
                                        <span class="badge {{ $typeClass }}">{{ $typeLabel }}</span>
                                    </div>
                                    <p class="mb-1 text-muted small">{{ Str::limit($event->description ?? '', 60) }}</p>
                                    <div class="d-flex align-items-center">
                                        <small class="text-muted me-3">
                                            <i class="fas fa-clock me-1"></i>
                                            @if(isset($event->date_debut) && isset($event->date_fin))
                                                {{ $event->date_debut->format('H:i') }} - {{ $event->date_fin->format('H:i') }}
                                            @else
                                                Horaire non défini
                                            @endif
                                        </small>
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            {{ $event->lieu ?? 'Lieu non défini' }}
                                        </small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    @php
                                        $statusClass = match($event->statut ?? 'planifie') {
                                            'termine' => 'bg-success',
                                            'en_cours' => 'bg-primary',
                                            'annule' => 'bg-danger',
                                            default => 'bg-warning'
                                        };
                                        $statusLabel = match($event->statut ?? 'planifie') {
                                            'termine' => 'Terminé',
                                            'en_cours' => 'En cours',
                                            'annule' => 'Annulé',
                                            default => 'Planifié'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                    <br>
                                    <small class="text-muted">Bientôt</small>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-calendar-times fa-2x mb-2"></i>
                            <br>Aucun événement prévu aujourd'hui
                            <br><small>Profitez de cette journée pour avancer sur vos tâches !</small>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Mes projets -->
        <div class="col-xl-8 col-lg-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-project-diagram text-success me-2"></i>
                        Mes projets actifs
                    </h5>
                    <a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-eye me-1"></i>
                        Voir tout
                    </a>
                </div>
                <div class="card-body">
                    @forelse($my_projects ?? [] as $project)
                    <div class="mb-3 @if(!$loop->last) border-bottom pb-3 @endif">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">{{ $project->nom ?? 'Projet sans nom' }}</h6>
                            @php
                                $projectStatusClass = match($project->statut ?? 'en_cours') {
                                    'termine' => 'bg-success',
                                    'en_cours' => 'bg-primary',
                                    'suspendu' => 'bg-warning',
                                    default => 'bg-secondary'
                                };
                                $projectStatusLabel = match($project->statut ?? 'en_cours') {
                                    'termine' => 'Terminé',
                                    'en_cours' => 'En cours',
                                    'suspendu' => 'Suspendu',
                                    default => 'Planifié'
                                };
                            @endphp
                            <span class="badge {{ $projectStatusClass }}">{{ $projectStatusLabel }}</span>
                        </div>
                        <p class="text-muted mb-2 small">{{ Str::limit($project->description ?? '', 100) }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    Échéance:
                                    @if(isset($project->date_fin) && $project->date_fin)
                                        {{ $project->date_fin->format('d/m/Y') }}
                                    @else
                                        Non définie
                                    @endif
                                </small>
                            </div>
                            <div class="text-end">
                                @php
                                    $tasks_count = $project->tasks_count ?? 0;
                                    $completed_tasks = $project->completed_tasks_count ?? 0;
                                    $progress = $tasks_count > 0 ? round(($completed_tasks / $tasks_count) * 100) : 0;
                                @endphp
                                <small class="text-muted">{{ $progress }}% terminé</small>
                                <div class="progress progress-sm mt-1 project-progress">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progress }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-folder-open fa-2x mb-2"></i>
                        <br>Vous n'êtes assigné à aucun projet actuellement
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="col-xl-4 col-lg-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt text-warning me-2"></i>
                        Actions rapides
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('reports.create') }}" class="btn btn-primary">
                            <i class="fas fa-file-alt me-2"></i>
                            Nouveau rapport d'intervention
                        </a>
                        <a href="{{ route('events.create') }}" class="btn btn-info">
                            <i class="fas fa-calendar-plus me-2"></i>
                            Planifier un événement
                        </a>
                        <a href="{{ route('tasks.index', ['status' => 'a_faire']) }}" class="btn btn-warning">
                            <i class="fas fa-list me-2"></i>
                            Voir mes tâches à faire
                        </a>
                        <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-user me-2"></i>
                            Mon profil
                        </a>
                    </div>
                </div>
            </div>

            <!-- Mes rapports récents -->
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-alt text-secondary me-2"></i>
                        Mes rapports récents
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($my_recent_reports ?? [] as $report)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ Str::limit($report->titre ?? 'Rapport', 30) }}</h6>
                                    <small class="text-muted">
                                        {{ $report->lieu ?? 'Lieu non défini' }} -
                                        @if(isset($report->date_intervention) && $report->date_intervention)
                                            {{ $report->date_intervention->format('d/m/Y') }}
                                        @else
                                            Date non définie
                                        @endif
                                    </small>
                                </div>
                                <small class="text-muted">Récent</small>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-3 text-muted">
                            <i class="fas fa-file-alt mb-2"></i>
                            <br><small>Aucun rapport récent</small>
                        </div>
                        @endforelse
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="fas fa-eye me-1"></i>
                        Voir tous mes rapports
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>
@endsection

@push('styles')
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stats-card {
    transition: transform 0.2s ease-in-out;
}

.stats-card:hover {
    transform: translateY(-2px);
}

.progress-sm {
    height: 4px;
}

.project-progress {
    width: 120px;
}

.opacity-75 {
    opacity: 0.75;
}

.card {
    border-radius: 10px;
}

.btn {
    border-radius: 6px;
}

.gap-2 {
    gap: 0.5rem;
}

.task-item {
    transition: background-color 0.2s ease;
}

.task-item:hover {
    background-color: #f8f9fa;
}

.badge {
    font-size: 0.75em;
}

.dropdown-item {
    transition: background-color 0.15s ease-in-out;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
}

.list-group-item {
    border-left: none;
    border-right: none;
}

.list-group-item:first-child {
    border-top: none;
}

.list-group-item:last-child {
    border-bottom: none;
}

/* Animation pour les stats */
@keyframes countUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.stats-card .card-body h2 {
    animation: countUp 0.5s ease-out;
}
</style>
@endpush

@push('scripts')
<script>
// Configuration globale pour éviter les conflits
window.PlanifTechDashboard = {
    config: {
        completedTasks: {{ $stats['completed_tasks'] ?? 0 }},
        csrfToken: "{{ csrf_token() }}",
        apiUrls: {
            dashboard: "{{ route('api.dashboard.data') }}",
            taskStatus: "/tasks/{id}/status"
        }
    },

    // Initialisation
    init: function() {
        this.bindEvents();
        this.showMotivationalMessage();
        this.startAutoRefresh();
    },

    // Liaison des événements
    bindEvents: function() {
        // Bouton de rafraîchissement
        const refreshBtn = document.getElementById('refreshBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                location.reload();
            });
        }

        // Actions sur les tâches
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('task-action') || e.target.closest('.task-action')) {
                e.preventDefault();
                const target = e.target.classList.contains('task-action') ? e.target : e.target.closest('.task-action');
                const taskId = target.dataset.taskId;
                const action = target.dataset.action;

                if (taskId && action) {
                    this.updateTaskStatus(parseInt(taskId), action);
                }
            }
        });
    },

    // Mise à jour du statut d'une tâche
    updateTaskStatus: function(taskId, status) {
        if (!taskId || !status) {
            this.showToast('Données invalides', 'danger');
            return;
        }

        const url = this.config.apiUrls.taskStatus.replace('{id}', taskId);

        fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.config.csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ statut: status })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                this.showToast(data.message || 'Tâche mise à jour avec succès', 'success');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                this.showToast(data.message || 'Erreur lors de la mise à jour', 'danger');
            }
        })
        .catch(error => {
            console.error('Erreur lors de la mise à jour de la tâche:', error);
            this.showToast('Erreur de communication avec le serveur', 'danger');
        });
    },

    // Affichage des messages toast
    showToast: function(message, type = 'info') {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toastId = 'toast-' + Date.now();
        const bgClass = {
            'success': 'bg-success',
            'danger': 'bg-danger',
            'warning': 'bg-warning',
            'info': 'bg-info'
        }[type] || 'bg-info';

        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', toastHtml);

        const toastElement = document.getElementById(toastId);
        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            const toast = new bootstrap.Toast(toastElement, {
                autohide: true,
                delay: 4000
            });
            toast.show();

            toastElement.addEventListener('hidden.bs.toast', () => {
                toastElement.remove();
            });
        } else {
            // Fallback si Bootstrap n'est pas disponible
            toastElement.style.display = 'block';
            setTimeout(() => {
                toastElement.remove();
            }, 4000);
        }
    },

    // Message de motivation
    showMotivationalMessage: function() {
        const messages = [
            "Excellent travail ! Continuez comme ça !",
            "Vous êtes sur la bonne voie !",
            "Chaque tâche terminée est un pas vers le succès !",
            "Votre contribution est précieuse pour l'ORMVAT !",
            "Bravo pour votre engagement !"
        ];

        const completedTasks = this.config.completedTasks;
        if (completedTasks > 0 && completedTasks % 5 === 0) {
            const randomMessage = messages[Math.floor(Math.random() * messages.length)];
            setTimeout(() => {
                this.showToast(randomMessage, 'success');
            }, 1000);
        }
    },

    // Actualisation automatique
    startAutoRefresh: function() {
        setInterval(() => {
            fetch(this.config.apiUrls.dashboard)
                .then(response => {
                    if (response.ok) {
                        return response.json();
                    }
                    throw new Error('Erreur de récupération des données');
                })
                .then(data => {
                    console.log('Données actualisées:', data);
                })
                .catch(error => {
                    console.warn('Erreur lors de l\'actualisation automatique:', error);
                });
        }, 300000); // 5 minutes
    }
};

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    window.PlanifTechDashboard.init();
});

// Gestion des erreurs globales
window.addEventListener('error', function(e) {
    console.error('Erreur JavaScript:', e.error);
});
</script>
@endpush
