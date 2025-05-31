@extends('layouts.app')

@section('title', 'Mon tableau de bord - PlanifTech ORMVAT')

@section('content')
<div class="container-fluid">
    <!-- En-tête personnalisé technicien -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-gradient-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h3 mb-2 fw-bold">
                                <i class="fas fa-sun text-warning me-2"></i>
                                Bonjour {{ auth()->user()->prenom }} !
                            </h1>
                            <p class="mb-0 opacity-75 fs-5">
                                Voici un aperçu de vos activités du jour - {{ now()->translatedFormat('l j F Y') }}
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="d-flex justify-content-end gap-2 flex-wrap">
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

    <!-- Mes statistiques personnelles -->
    <div class="row mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
            <div class="card stats-card border-0 shadow-sm h-100 animate-fade-in">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-title text-muted mb-1">Mes tâches</h6>
                            <h2 class="mb-0 text-primary fw-bold">{{ $stats['my_tasks'] ?? 0 }}</h2>
                            <small class="text-muted">{{ $stats['pending_tasks'] ?? 0 }} en attente</small>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                                <i class="fas fa-tasks text-primary fa-2x"></i>
                            </div>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        @php
                            $total = $stats['my_tasks'] ?? 1;
                            $pending = $stats['pending_tasks'] ?? 0;
                            $progress = $total > 0 ? (($total - $pending) / $total) * 100 : 0;
                        @endphp
                        <div class="progress-bar bg-primary" style="width: {{ $progress }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
            <div class="card stats-card border-0 shadow-sm h-100 animate-fade-in">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-title text-muted mb-1">Terminées</h6>
                            <h2 class="mb-0 text-success fw-bold">{{ $stats['completed_tasks'] ?? 0 }}</h2>
                            @php
                                $total = ($stats['my_tasks'] ?? 0) + ($stats['completed_tasks'] ?? 0);
                                $completed = $stats['completed_tasks'] ?? 0;
                                $rate = $total > 0 ? round(($completed / $total) * 100) : 0;
                            @endphp
                            <small class="text-success">{{ $rate }}% de réussite</small>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3">
                                <i class="fas fa-check-circle text-success fa-2x"></i>
                            </div>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-success" style="width: {{ $rate }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
            <div class="card stats-card border-0 shadow-sm h-100 animate-fade-in">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-title text-muted mb-1">En retard</h6>
                            <h2 class="mb-0 text-danger fw-bold">{{ $stats['overdue_tasks'] ?? 0 }}</h2>
                            @if(($stats['overdue_tasks'] ?? 0) > 0)
                                <small class="text-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Action requise
                                </small>
                            @else
                                <small class="text-success">
                                    <i class="fas fa-thumbs-up"></i>
                                    Tout est à jour !
                                </small>
                            @endif
                        </div>
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                                <i class="fas fa-exclamation-triangle text-danger fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
            <div class="card stats-card border-0 shadow-sm h-100 animate-fade-in">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-title text-muted mb-1">Événements aujourd'hui</h6>
                            <h2 class="mb-0 text-info fw-bold">{{ $stats['my_events_today'] ?? 0 }}</h2>
                            <small class="text-muted">{{ $stats['my_reports_week'] ?? 0 }} rapports cette semaine</small>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-info bg-opacity-10 p-3">
                                <i class="fas fa-calendar-day text-info fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Mes tâches prioritaires -->
        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="card shadow-sm border-0 h-100 animate-fade-in">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-semibold">
                            <i class="fas fa-star text-warning me-2"></i>
                            Mes tâches prioritaires
                        </h5>
                        <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye me-1"></i>
                            Voir tout
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @forelse($my_priority_tasks ?? [] as $task)
                        <div class="border-bottom p-3 task-item hover-bg-light"
                             data-task-id="{{ $task->id }}"
                             data-task-title="{{ $task->titre }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="form-check me-3">
                                            <input class="form-check-input task-checkbox" type="checkbox"
                                                   id="task-{{ $task->id }}"
                                                   {{ $task->statut === 'termine' ? 'checked' : '' }}
                                                   data-task-id="{{ $task->id }}">
                                        </div>
                                        <h6 class="mb-0 me-2 {{ $task->statut === 'termine' ? 'text-decoration-line-through text-muted' : '' }}">
                                            {{ $task->titre }}
                                        </h6>
                                        @php
                                            $priorityClass = match($task->priorite) {
                                                'haute' => 'bg-danger',
                                                'urgente' => 'bg-danger',
                                                'moyenne' => 'bg-warning',
                                                'basse' => 'bg-success',
                                                default => 'bg-secondary'
                                            };
                                            $priorityLabel = match($task->priorite) {
                                                'haute' => 'Haute',
                                                'urgente' => 'Urgente',
                                                'moyenne' => 'Moyenne',
                                                'basse' => 'Basse',
                                                default => 'Normale'
                                            };
                                        @endphp
                                        <span class="badge {{ $priorityClass }}">{{ $priorityLabel }}</span>
                                    </div>
                                    <p class="mb-2 text-muted small">{{ Str::limit($task->description, 80) }}</p>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            @if($task->project)
                                                <span class="badge bg-light text-dark me-2">
                                                    <i class="fas fa-project-diagram me-1"></i>
                                                    {{ Str::limit($task->project->nom, 20) }}
                                                </span>
                                            @endif
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                @if($task->date_echeance)
                                                    {{ $task->date_echeance->format('d/m/Y') }}
                                                    @if($task->isOverdue())
                                                        <span class="text-danger">(En retard)</span>
                                                    @elseif($task->isDueToday())
                                                        <span class="text-warning">(Aujourd'hui)</span>
                                                    @endif
                                                @else
                                                    Non définie
                                                @endif
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="{{ route('tasks.show', $task->id) }}">
                                                        <i class="fas fa-eye me-2"></i>Voir détails
                                                    </a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    @if($task->statut !== 'en_cours')
                                                        <li><a class="dropdown-item task-action"
                                                               data-action="en_cours"
                                                               data-task-id="{{ $task->id }}"
                                                               href="#">
                                                            <i class="fas fa-play me-2 text-primary"></i>Commencer
                                                        </a></li>
                                                    @endif
                                                    @if($task->statut !== 'termine')
                                                        <li><a class="dropdown-item task-action"
                                                               data-action="termine"
                                                               data-task-id="{{ $task->id }}"
                                                               href="#">
                                                            <i class="fas fa-check me-2 text-success"></i>Terminer
                                                        </a></li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Barre de progression -->
                                    <div class="progress mt-2" style="height: 6px;">
                                        @php
                                            $progression = $task->progression ?? 0;
                                            $statusColor = match($task->statut) {
                                                'termine' => 'bg-success',
                                                'en_cours' => 'bg-primary',
                                                'a_faire' => 'bg-secondary',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <div class="progress-bar {{ $statusColor }}" style="width: {{ $progression }}%">
                                            <span class="visually-hidden">{{ $progression }}% terminé</span>
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $progression }}% terminé</small>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-trophy fa-3x mb-3 text-success"></i>
                            <h6>Aucune tâche prioritaire en attente</h6>
                            <p class="mb-0">Excellent travail ! Vous êtes à jour sur toutes vos tâches importantes.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Mes événements du jour -->
        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="card shadow-sm border-0 h-100 animate-fade-in">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-semibold">
                            <i class="fas fa-calendar-day text-info me-2"></i>
                            Mes événements du jour
                        </h5>
                        <a href="{{ route('events.calendar') }}" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-calendar me-1"></i>
                            Calendrier
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @forelse($my_today_events ?? [] as $event)
                        <div class="border-bottom p-3 hover-bg-light">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <h6 class="mb-0 me-2">{{ $event->titre }}</h6>
                                        @php
                                            $typeClass = match($event->type) {
                                                'intervention' => 'bg-danger',
                                                'reunion' => 'bg-primary',
                                                'formation' => 'bg-success',
                                                'visite' => 'bg-info',
                                                'maintenance' => 'bg-warning',
                                                default => 'bg-secondary'
                                            };
                                            $typeLabel = match($event->type) {
                                                'intervention' => 'Intervention',
                                                'reunion' => 'Réunion',
                                                'formation' => 'Formation',
                                                'visite' => 'Visite',
                                                'maintenance' => 'Maintenance',
                                                default => 'Événement'
                                            };
                                        @endphp
                                        <span class="badge {{ $typeClass }}">{{ $typeLabel }}</span>
                                    </div>
                                    <p class="mb-2 text-muted small">{{ Str::limit($event->description, 60) }}</p>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                @if($event->date_debut && $event->date_fin)
                                                    {{ $event->date_debut->format('H:i') }} - {{ $event->date_fin->format('H:i') }}
                                                @else
                                                    Horaire non défini
                                                @endif
                                            </small>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                {{ $event->lieu ?? 'Lieu non défini' }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    @php
                                        $statusClass = match($event->statut) {
                                            'termine' => 'bg-success',
                                            'en_cours' => 'bg-primary',
                                            'annule' => 'bg-danger',
                                            'reporte' => 'bg-warning',
                                            default => 'bg-secondary'
                                        };
                                        $statusLabel = match($event->statut) {
                                            'termine' => 'Terminé',
                                            'en_cours' => 'En cours',
                                            'annule' => 'Annulé',
                                            'reporte' => 'Reporté',
                                            default => 'Planifié'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                    <br>
                                    @if($event->date_debut && $event->date_debut->isFuture())
                                        <small class="text-muted">
                                            Dans {{ $event->date_debut->diffForHumans() }}
                                        </small>
                                    @elseif($event->date_debut && $event->date_debut->isPast())
                                        <small class="text-muted">
                                            Il y a {{ $event->date_debut->diffForHumans() }}
                                        </small>
                                    @else
                                        <small class="text-success">Maintenant</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-calendar-check fa-3x mb-3"></i>
                            <h6>Aucun événement prévu aujourd'hui</h6>
                            <p class="mb-0">Profitez de cette journée libre pour avancer sur vos tâches !</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Mes projets -->
        <div class="col-xl-8 col-lg-12 mb-4">
            <div class="card shadow-sm border-0 animate-fade-in">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-semibold">
                            <i class="fas fa-project-diagram text-success me-2"></i>
                            Mes projets actifs
                        </h5>
                        <a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-eye me-1"></i>
                            Voir tout
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @forelse($my_projects ?? [] as $project)
                        <div class="border rounded-3 p-3 mb-3 hover-bg-light {{ !$loop->last ? 'mb-3' : '' }}">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <h6 class="mb-0 me-2">{{ $project->nom }}</h6>
                                        @php
                                            $projectStatusClass = match($project->statut) {
                                                'termine' => 'bg-success',
                                                'en_cours' => 'bg-primary',
                                                'suspendu' => 'bg-warning',
                                                'annule' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                            $projectStatusLabel = match($project->statut) {
                                                'termine' => 'Terminé',
                                                'en_cours' => 'En cours',
                                                'suspendu' => 'Suspendu',
                                                'annule' => 'Annulé',
                                                default => 'Planifié'
                                            };
                                        @endphp
                                        <span class="badge {{ $projectStatusClass }}">{{ $projectStatusLabel }}</span>
                                    </div>
                                    <p class="text-muted mb-2 small">{{ Str::limit($project->description, 100) }}</p>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                Échéance:
                                                @if($project->date_fin)
                                                    {{ $project->date_fin->format('d/m/Y') }}
                                                    @if($project->isOverdue())
                                                        <span class="text-danger">(En retard)</span>
                                                    @endif
                                                @else
                                                    Non définie
                                                @endif
                                            </small>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                {{ $project->zone_geographique ?? 'Zone non définie' }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end ms-3">
                                    @php
                                        $tasks_count = $project->tasks_count ?? 0;
                                        $completed_tasks = $project->completed_tasks_count ?? 0;
                                        $progress = $tasks_count > 0 ? round(($completed_tasks / $tasks_count) * 100) : $project->pourcentage_avancement;
                                    @endphp
                                    <div class="mb-1">
                                        <small class="text-muted">{{ $progress }}% terminé</small>
                                    </div>
                                    <div class="progress" style="width: 120px; height: 8px;">
                                        <div class="progress-bar bg-success" style="width: {{ $progress }}%"></div>
                                    </div>
                                    <small class="text-muted">
                                        {{ $completed_tasks }}/{{ $tasks_count }} tâches
                                    </small>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <a href="{{ route('projects.show', $project->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> Voir détails
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-folder-open fa-3x mb-3"></i>
                            <h6>Vous n'êtes assigné à aucun projet actuellement</h6>
                            <p class="mb-0">Contactez votre responsable pour plus d'informations.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Actions rapides et rapports récents -->
        <div class="col-xl-4 col-lg-12 mb-4">
            <!-- Actions rapides -->
            <div class="card shadow-sm border-0 mb-4 animate-fade-in">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="card-title mb-0 fw-semibold">
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
            <div class="card shadow-sm border-0 animate-fade-in">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-semibold">
                            <i class="fas fa-file-alt text-secondary me-2"></i>
                            Mes rapports récents
                        </h5>
                        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-eye me-1"></i>
                            Tous
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @forelse($my_recent_reports ?? [] as $report)
                        <div class="border-bottom p-3 hover-bg-light">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ Str::limit($report->titre, 30) }}</h6>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            {{ Str::limit($report->lieu, 20) }}
                                        </small>
                                        <span class="badge bg-{{ $report->status_color }}">
                                            {{ $report->status_label }}
                                        </span>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        @if($report->date_intervention)
                                            {{ $report->date_intervention->format('d/m/Y') }}
                                        @else
                                            Date non définie
                                        @endif
                                    </small>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-3 text-muted">
                            <i class="fas fa-file-alt fa-2x mb-2"></i>
                            <p class="mb-0 small">Aucun rapport récent</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container pour les notifications -->
<div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>
@endsection

@push('styles')
<style>
.hover-bg-light {
    transition: background-color 0.2s ease;
}

.hover-bg-light:hover {
    background-color: #f8f9fa !important;
}

.task-item {
    transition: all 0.2s ease;
    cursor: pointer;
}

.task-item:hover {
    transform: translateX(5px);
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.stats-card {
    transition: transform 0.2s ease-in-out;
}

.stats-card:hover {
    transform: translateY(-5px);
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
    border-radius: 15px;
    border: none;
}

.btn {
    border-radius: 8px;
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeInUp 0.6s ease-out;
}

.task-checkbox {
    transform: scale(1.2);
}

.badge {
    font-size: 0.75em;
    padding: 0.4em 0.6em;
}
</style>
@endpush

@push('scripts')
<script>
// Configuration globale pour éviter les conflits
window.PlanifTechTechnicien = {
    config: {
        completedTasks: {{ $stats['completed_tasks'] ?? 0 }},
        csrfToken: "{{ csrf_token() }}",
        apiUrls: {
            dashboard: "{{ route('api.dashboard.data') }}",
            taskStatus: "/api/tasks/{id}/status",
            quickUpdate: "/api/tasks/{id}/quick-update"
        }
    },

    // Initialisation
    init: function() {
        this.bindEvents();
        this.showMotivationalMessage();
        this.startAutoRefresh();
        this.animateCards();
    },

    // Liaison des événements
    bindEvents: function() {
        // Bouton de rafraîchissement
        const refreshBtn = document.getElementById('refreshBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.refreshDashboard();
            });
        }

        // Checkboxes des tâches
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('task-checkbox')) {
                const taskId = parseInt(e.target.dataset.taskId);
                const completed = e.target.checked;
                this.updateTaskStatus(taskId, completed ? 'termine' : 'a_faire');
            }
        });

        // Actions sur les tâches
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('task-action') || e.target.closest('.task-action')) {
                e.preventDefault();
                const target = e.target.classList.contains('task-action') ? e.target : e.target.closest('.task-action');
                const taskId = parseInt(target.dataset.taskId);
                const action = target.dataset.action;

                if (taskId && action) {
                    this.updateTaskStatus(taskId, action);
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

        const url = this.config.apiUrls.quickUpdate.replace('{id}', taskId);

        // Afficher un indicateur de chargement
        this.showToast('Mise à jour en cours...', 'info');

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
                // Actualiser après un délai
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

        const iconClass = {
            'success': 'fa-check-circle',
            'danger': 'fa-exclamation-circle',
            'warning': 'fa-exclamation-triangle',
            'info': 'fa-info-circle'
        }[type] || 'fa-info-circle';

        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas ${iconClass} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', toastHtml);

        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: 4000
        });
        toast.show();

        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    },

    // Message de motivation
    showMotivationalMessage: function() {
        const messages = [
            "Excellent travail ! Continuez comme ça !",
            "Vous êtes sur la bonne voie !",
            "Chaque tâche terminée est un pas vers le succès !",
            "Votre contribution est précieuse pour l'ORMVAT !",
            "Bravo pour votre engagement !",
            "Gardez ce rythme, c'est parfait !",
            "Vous faites un travail remarquable !"
        ];

        const completedTasks = this.config.completedTasks;
        if (completedTasks > 0 && completedTasks % 5 === 0) {
            const randomMessage = messages[Math.floor(Math.random() * messages.length)];
            setTimeout(() => {
                this.showToast(randomMessage, 'success');
            }, 2000);
        }
    },

    // Actualiser le dashboard
    refreshDashboard: function() {
        const refreshBtn = document.getElementById('refreshBtn');
        if (refreshBtn) {
            const originalContent = refreshBtn.innerHTML;
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualisation...';
            refreshBtn.disabled = true;

            setTimeout(() => {
                location.reload();
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
                    // Ici on pourrait mettre à jour certaines parties sans recharger
                })
                .catch(error => {
                    console.warn('Erreur lors de l\'actualisation automatique:', error);
                });
        }, 300000); // 5 minutes
    },

    // Animation des cartes
    animateCards: function() {
        const cards = document.querySelectorAll('.animate-fade-in');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });
    }
};

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    window.PlanifTechTechnicien.init();
});

// Gestion des erreurs globales
window.addEventListener('error', function(e) {
    console.error('Erreur JavaScript:', e.error);
});

// Animation d'apparition progressive
document.addEventListener('DOMContentLoaded', function() {
    const elements = document.querySelectorAll('.animate-fade-in');
    elements.forEach((el, index) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';

        setTimeout(() => {
            el.style.transition = 'all 0.6s ease';
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
</script>
@endpush
@endsection
