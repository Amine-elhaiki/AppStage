@extends('layouts.app')

@section('title', 'Tableau de bord - PlanifTech ORMVAT')

@section('content')
<div class="container-fluid py-4">
    <!-- En-tête du dashboard -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-dark">
                        <i class="fas fa-tachometer-alt me-2 text-primary"></i>
                        Tableau de bord
                    </h1>
                    <p class="text-muted mb-0">
                        Bonjour {{ auth()->check() ? auth()->user()->prenom : 'Utilisateur' }}, voici votre aperçu quotidien des activités ORMVAT
                    </p>
                </div>
                <div class="text-end">
                    <div class="small text-muted">
                        <i class="fas fa-clock me-1"></i>
                        {{ now()->translatedFormat('l j F Y, H:i') }}
                    </div>
                    <div class="small text-muted">
                        <i class="fas fa-map-marker-alt me-1"></i>
                        ORMVAT - Tadla
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                                <i class="fas fa-tasks text-primary fa-lg"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h5 class="mb-1 fw-bold">{{ isset($stats['my_tasks']) ? $stats['my_tasks'] : 0 }}</h5>
                            <p class="text-muted mb-0 small">Mes tâches actives</p>
                            @if(isset($stats['overdue_tasks']) && $stats['overdue_tasks'] > 0)
                                <small class="text-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    {{ $stats['overdue_tasks'] }} en retard
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3">
                                <i class="fas fa-calendar-alt text-success fa-lg"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h5 class="mb-1 fw-bold">{{ isset($stats['today_events']) ? $stats['today_events'] : 0 }}</h5>
                            <p class="text-muted mb-0 small">Événements aujourd'hui</p>
                            @if(isset($stats['upcoming_events']) && $stats['upcoming_events'] > 0)
                                <small class="text-info">
                                    <i class="fas fa-clock"></i>
                                    {{ $stats['upcoming_events'] }} à venir
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-info bg-opacity-10 p-3">
                                <i class="fas fa-project-diagram text-info fa-lg"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h5 class="mb-1 fw-bold">{{ isset($stats['active_projects']) ? $stats['active_projects'] : 0 }}</h5>
                            <p class="text-muted mb-0 small">Projets actifs</p>
                            @if(isset($stats['project_completion']))
                                <small class="text-success">
                                    <i class="fas fa-chart-line"></i>
                                    {{ $stats['project_completion'] }}% moyen
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                                <i class="fas fa-file-alt text-warning fa-lg"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h5 class="mb-1 fw-bold">{{ isset($stats['recent_reports']) ? $stats['recent_reports'] : 0 }}</h5>
                            <p class="text-muted mb-0 small">Rapports ce mois</p>
                            @if(isset($stats['pending_reports']) && $stats['pending_reports'] > 0)
                                <small class="text-warning">
                                    <i class="fas fa-hourglass-half"></i>
                                    {{ $stats['pending_reports'] }} en attente
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Section principale -->
        <div class="col-lg-8">
            <!-- Mes tâches prioritaires -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-star text-warning me-2"></i>
                            Mes tâches prioritaires
                        </h5>
                        <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-primary">
                            Voir toutes <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($priority_tasks) && count($priority_tasks) > 0)
                        @foreach($priority_tasks as $task)
                            <div class="d-flex align-items-center p-3 mb-2 rounded-3 bg-light">
                                <div class="flex-shrink-0">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               id="task-{{ $task->id ?? 0 }}"
                                               {{ isset($task->statut) && $task->statut === 'termine' ? 'checked' : '' }}
                                               onchange="updateTaskStatus({{ $task->id ?? 0 }}, this.checked)">
                                    </div>
                                </div>
                                <div class="ms-3 flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1 {{ isset($task->statut) && $task->statut === 'termine' ? 'text-decoration-line-through text-muted' : '' }}">
                                                {{ $task->titre ?? 'Sans titre' }}
                                            </h6>
                                            <p class="text-muted small mb-1">{{ isset($task->description) ? Str::limit($task->description, 100) : 'Aucune description' }}</p>
                                            <div class="small">
                                                @php
                                                    $priorite = $task->priorite ?? 'basse';
                                                    $prioriteClass = match($priorite) {
                                                        'haute' => 'danger',
                                                        'moyenne' => 'warning',
                                                        default => 'secondary'
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $prioriteClass }}">
                                                    {{ ucfirst($priorite) }}
                                                </span>
                                                <span class="text-muted ms-2">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    @if(isset($task->date_echeance) && $task->date_echeance)
                                                        {{ \Carbon\Carbon::parse($task->date_echeance)->format('d/m/Y') }}
                                                    @else
                                                        Pas de date
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <a href="{{ route('tasks.show', $task->id ?? 0) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                            <p class="mb-0">Aucune tâche prioritaire en cours !</p>
                            <small>Toutes vos tâches importantes sont terminées.</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Projets en cours -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-project-diagram text-info me-2"></i>
                            Projets en cours
                        </h5>
                        <a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-primary">
                            Voir tous <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($active_projects) && count($active_projects) > 0)
                        @foreach($active_projects as $project)
                            <div class="mb-3 p-3 border rounded-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1">{{ $project->nom ?? 'Projet sans nom' }}</h6>
                                        <p class="text-muted small mb-2">{{ isset($project->description) ? Str::limit($project->description, 80) : 'Aucune description' }}</p>
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            {{ $project->zone_geographique ?? 'Zone non définie' }}
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        @php
                                            $avancement = isset($project->pourcentage_avancement) ? $project->pourcentage_avancement : 0;
                                        @endphp
                                        <div class="small text-muted mb-1">{{ $avancement }}%</div>
                                        <div class="progress" style="width: 100px; height: 6px;">
                                            <div class="progress-bar" role="progressbar"
                                                 style="width: {{ $avancement }}%"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="small text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        Échéance:
                                        @if(isset($project->date_fin) && $project->date_fin)
                                            {{ \Carbon\Carbon::parse($project->date_fin)->format('d/m/Y') }}
                                        @else
                                            Non définie
                                        @endif
                                    </div>
                                    <a href="{{ route('projects.show', $project->id ?? 0) }}" class="btn btn-sm btn-outline-primary">
                                        Détails
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-folder-open fa-3x mb-3"></i>
                            <p class="mb-0">Aucun projet actif</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar droite -->
        <div class="col-lg-4">
            <!-- Événements du jour -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-calendar-day text-success me-2"></i>
                        Agenda du jour
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($today_events) && count($today_events) > 0)
                        @foreach($today_events as $event)
                            <div class="d-flex align-items-center p-2 mb-2 rounded bg-light">
                                <div class="flex-shrink-0">
                                    @php
                                        $eventType = $event->type ?? 'autre';
                                        $eventIcon = match($eventType) {
                                            'reunion' => 'users',
                                            'intervention' => 'tools',
                                            'formation' => 'chalkboard-teacher',
                                            default => 'calendar'
                                        };
                                    @endphp
                                    <div class="rounded-circle bg-success bg-opacity-10 p-2">
                                        <i class="fas fa-{{ $eventIcon }} text-success"></i>
                                    </div>
                                </div>
                                <div class="ms-3 flex-grow-1">
                                    <h6 class="mb-1">{{ $event->titre ?? 'Événement sans titre' }}</h6>
                                    <div class="small text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        @if(isset($event->date_debut) && isset($event->date_fin))
                                            {{ \Carbon\Carbon::parse($event->date_debut)->format('H:i') }} -
                                            {{ \Carbon\Carbon::parse($event->date_fin)->format('H:i') }}
                                        @else
                                            Horaire non défini
                                        @endif
                                    </div>
                                    <div class="small text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        {{ $event->lieu ?? 'Lieu non défini' }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-3 text-muted">
                            <i class="fas fa-calendar-check fa-2x mb-2"></i>
                            <p class="mb-0 small">Aucun événement prévu aujourd'hui</p>
                        </div>
                    @endif

                    <div class="text-center mt-3">
                        <a href="{{ route('events.calendar') }}" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-calendar-alt me-1"></i>
                            Voir le calendrier
                        </a>
                    </div>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-bolt text-warning me-2"></i>
                        Actions rapides
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @auth
                            @if(auth()->user()->role === 'admin')
                                <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Nouvelle tâche
                                </a>
                                <a href="{{ route('projects.create') }}" class="btn btn-info">
                                    <i class="fas fa-project-diagram me-2"></i>Nouveau projet
                                </a>
                            @endif
                        @endauth
                        <a href="{{ route('events.create') }}" class="btn btn-success">
                            <i class="fas fa-calendar-plus me-2"></i>Nouvel événement
                        </a>
                        <a href="{{ route('reports.create') }}" class="btn btn-warning text-white">
                            <i class="fas fa-file-plus me-2"></i>Nouveau rapport
                        </a>
                    </div>
                </div>
            </div>

            <!-- Notifications/Alertes -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-bell text-info me-2"></i>
                        Notifications
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($notifications) && count($notifications) > 0)
                        @foreach($notifications as $notification)
                            @php
                                $notifType = isset($notification->type) ? $notification->type : 'info';
                                $alertClass = $notifType === 'urgent' ? 'danger' : 'info';
                                $iconClass = $notifType === 'urgent' ? 'exclamation-triangle' : 'info-circle';
                            @endphp
                            <div class="alert alert-{{ $alertClass }} alert-dismissible fade show" role="alert">
                                <i class="fas fa-{{ $iconClass }} me-2"></i>
                                <strong>{{ $notification->title ?? 'Notification' }}</strong>
                                <p class="mb-0 small">{{ $notification->message ?? 'Pas de message' }}</p>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-3 text-muted">
                            <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                            <p class="mb-0 small">Aucune notification</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function updateTaskStatus(taskId, completed) {
    // Vérification de l'ID de tâche
    if (!taskId || taskId <= 0) {
        console.error('ID de tâche invalide');
        return;
    }

    const status = completed ? 'termine' : 'en_cours';

    fetch(`/api/tasks/${taskId}/quick-update`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({ statut: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Réactualiser la page après un court délai
            setTimeout(() => location.reload(), 500);
        } else {
            alert('Erreur lors de la mise à jour');
            // Remettre la case à cocher dans son état précédent
            const checkbox = document.getElementById(`task-${taskId}`);
            if (checkbox) {
                checkbox.checked = !completed;
            }
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur de connexion');
        const checkbox = document.getElementById(`task-${taskId}`);
        if (checkbox) {
            checkbox.checked = !completed;
        }
    });
}

// Auto-refresh des statistiques toutes les 5 minutes
setInterval(() => {
    fetch('/api/dashboard/stats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Statistiques mises à jour');
            }
        })
        .catch(error => console.log('Erreur refresh stats:', error));
}, 300000); // 5 minutes
</script>
@endpush
@endsection
