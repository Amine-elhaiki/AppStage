@extends('layouts.app')

@section('title', ($task->titre ?? 'Tâche') . ' - PlanifTech ORMVAT')

@section('content')
<div class="container-fluid py-4">
    <!-- En-tête -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Tableau de bord</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('tasks.index') }}">Tâches</a>
                    </li>
                    <li class="breadcrumb-item active">{{ isset($task->titre) ? Str::limit($task->titre, 40) : 'Tâche' }}</li>
                </ol>
            </nav>

            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h1 class="h3 mb-2 text-dark">{{ $task->titre ?? 'Tâche sans titre' }}</h1>
                    <div class="d-flex align-items-center gap-3 mb-2">
                        @php
                            $statut = $task->statut ?? 'a_faire';
                            $statutClass = match($statut) {
                                'termine' => 'success',
                                'en_cours' => 'primary',
                                default => 'secondary'
                            };

                            $priorite = $task->priorite ?? 'basse';
                            $prioriteClass = match($priorite) {
                                'haute' => 'danger',
                                'moyenne' => 'warning',
                                default => 'secondary'
                            };
                        @endphp

                        <span class="badge bg-{{ $statutClass }} fs-6">
                            {{ ucfirst(str_replace('_', ' ', $statut)) }}
                        </span>
                        <span class="badge bg-{{ $prioriteClass }} fs-6">
                            Priorité {{ ucfirst($priorite) }}
                        </span>

                        @if(isset($task->date_echeance) && $task->date_echeance)
                            @php
                                $dateEcheance = \Carbon\Carbon::parse($task->date_echeance);
                                $isOverdue = $dateEcheance->isPast() && $statut !== 'termine';
                            @endphp
                            @if($isOverdue)
                                <span class="badge bg-danger fs-6">
                                    <i class="fas fa-exclamation-triangle me-1"></i>En retard
                                </span>
                            @endif
                        @endif
                    </div>
                    <p class="text-muted mb-0">
                        Tâche #{{ $task->id ?? 'N/A' }} •
                        Créée le {{ isset($task->created_at) ? $task->created_at->format('d/m/Y') : 'N/A' }} •
                        Dernière modification {{ isset($task->updated_at) ? $task->updated_at->diffForHumans() : 'N/A' }}
                    </p>
                </div>

                <div class="btn-group" role="group">
                    @auth
                        @if(auth()->user()->role === 'admin' || (isset($task->id_utilisateur) && auth()->id() === $task->id_utilisateur))
                            <a href="{{ route('tasks.edit', $task->id ?? 0) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i>Modifier
                            </a>
                        @endif

                        @if($statut !== 'termine' && (auth()->user()->role === 'admin' || (isset($task->id_utilisateur) && auth()->id() === $task->id_utilisateur)))
                            <button type="button" class="btn btn-success" onclick="markAsCompleted()">
                                <i class="fas fa-check me-2"></i>Marquer terminé
                            </button>
                        @endif
                    @endauth

                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('reports.create', ['task_id' => $task->id ?? 0]) }}">
                                    <i class="fas fa-file-plus me-2"></i>Créer un rapport
                                </a>
                            </li>
                            @auth
                                @if(auth()->user()->role === 'admin')
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button class="dropdown-item text-danger" onclick="deleteTask()">
                                            <i class="fas fa-trash me-2"></i>Supprimer
                                        </button>
                                    </li>
                                @endif
                            @endauth
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Contenu principal -->
        <div class="col-lg-8">
            <!-- Description -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-align-left me-2 text-primary"></i>
                        Description de la tâche
                    </h5>
                </div>
                <div class="card-body">
                    <div class="fs-6 lh-lg">
                        @if(isset($task->description) && $task->description)
                            {!! nl2br(e($task->description)) !!}
                        @else
                            <em class="text-muted">Aucune description disponible</em>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Progression -->
            @php
                $progression = isset($task->progression) ? $task->progression : 0;
            @endphp
            @if($progression > 0 || $statut === 'en_cours')
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-semibold">
                                <i class="fas fa-chart-line me-2 text-success"></i>
                                Progression
                            </h5>
                            <span class="fw-bold text-success">{{ $progression }}%</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="progress mb-3" style="height: 12px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                 role="progressbar"
                                 style="width: {{ $progression }}%"></div>
                        </div>

                        @auth
                            @if(auth()->user()->role === 'admin' || (isset($task->id_utilisateur) && auth()->id() === $task->id_utilisateur))
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="updateProgress(25)">25%</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="updateProgress(50)">50%</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="updateProgress(75)">75%</button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="updateProgress(100)">100%</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="customProgress()">Personnalisé</button>
                                </div>
                            @endif
                        @endauth
                    </div>
                </div>
            @endif

            <!-- Rapports associés -->
            @if(isset($task->rapports) && $task->rapports && $task->rapports->count() > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-semibold">
                                <i class="fas fa-file-alt me-2 text-info"></i>
                                Rapports d'intervention ({{ $task->rapports->count() }})
                            </h5>
                            <a href="{{ route('reports.create', ['task_id' => $task->id ?? 0]) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i>Nouveau rapport
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @foreach($task->rapports as $rapport)
                            <div class="d-flex align-items-center p-3 mb-2 border rounded">
                                <div class="flex-shrink-0 me-3">
                                    <div class="rounded-circle bg-info bg-opacity-10 p-2">
                                        <i class="fas fa-file-alt text-info"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $rapport->titre ?? 'Rapport sans titre' }}</h6>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        @if(isset($rapport->date_intervention))
                                            {{ \Carbon\Carbon::parse($rapport->date_intervention)->format('d/m/Y') }}
                                        @else
                                            Date non définie
                                        @endif
                                        <i class="fas fa-user ms-2 me-1"></i>
                                        @if(isset($rapport->user) && $rapport->user)
                                            {{ $rapport->user->prenom ?? '' }} {{ $rapport->user->nom ?? '' }}
                                        @else
                                            Auteur inconnu
                                        @endif
                                    </small>
                                </div>
                                <div>
                                    <a href="{{ route('reports.show', $rapport->id ?? 0) }}" class="btn btn-sm btn-outline-primary">
                                        Voir
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Commentaires/Notes -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-comments me-2 text-secondary"></i>
                        Notes et commentaires
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($task->commentaires) && count($task->commentaires) > 0)
                        @foreach($task->commentaires as $commentaire)
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0 me-3">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-2">
                                        <i class="fas fa-user text-primary"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <strong>
                                            @if(isset($commentaire->user) && $commentaire->user)
                                                {{ $commentaire->user->prenom ?? '' }} {{ $commentaire->user->nom ?? '' }}
                                            @else
                                                Utilisateur inconnu
                                            @endif
                                        </strong>
                                        <small class="text-muted">
                                            {{ isset($commentaire->created_at) ? $commentaire->created_at->diffForHumans() : 'Date inconnue' }}
                                        </small>
                                    </div>
                                    <p class="mb-0">{{ $commentaire->contenu ?? 'Commentaire vide' }}</p>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-3 text-muted">
                            <i class="fas fa-comment-slash fa-2x mb-2"></i>
                            <p class="mb-0">Aucun commentaire pour le moment</p>
                        </div>
                    @endif

                    <!-- Formulaire d'ajout de commentaire -->
                    @auth
                        @if(auth()->user()->role === 'admin' || (isset($task->id_utilisateur) && auth()->id() === $task->id_utilisateur))
                            <hr>
                            <form method="POST" action="{{ route('tasks.comment', $task->id ?? 0) }}">
                                @csrf
                                <div class="mb-3">
                                    <textarea class="form-control"
                                              name="commentaire"
                                              rows="3"
                                              placeholder="Ajouter une note ou un commentaire..."
                                              required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-comment me-2"></i>Ajouter un commentaire
                                </button>
                            </form>
                        @endif
                    @endauth
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Informations de la tâche -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-info-circle me-2"></i>
                        Informations de la tâche
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-5"><strong>Statut :</strong></div>
                        <div class="col-7">
                            <span class="badge bg-{{ $statutClass }}">
                                {{ ucfirst(str_replace('_', ' ', $statut)) }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-5"><strong>Priorité :</strong></div>
                        <div class="col-7">
                            <span class="badge bg-{{ $prioriteClass }}">
                                {{ ucfirst($priorite) }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-5"><strong>Assigné à :</strong></div>
                        <div class="col-7">
                            @if(isset($task->user) && $task->user)
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-1 me-2">
                                        <i class="fas fa-user text-primary small"></i>
                                    </div>
                                    <div>
                                        <div class="small fw-semibold">{{ $task->user->prenom ?? '' }} {{ $task->user->nom ?? '' }}</div>
                                        <div class="small text-muted">{{ ucfirst($task->user->role ?? '') }}</div>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">Non assigné</span>
                            @endif
                        </div>
                    </div>

                    @if(isset($task->date_echeance) && $task->date_echeance)
                        <div class="row mb-3">
                            <div class="col-5"><strong>Échéance :</strong></div>
                            <div class="col-7">
                                @php
                                    $dateEcheance = \Carbon\Carbon::parse($task->date_echeance);
                                    $isOverdue = $dateEcheance->isPast() && $statut !== 'termine';
                                @endphp
                                <div class="small">
                                    {{ $dateEcheance->format('d/m/Y') }}
                                    @if($isOverdue)
                                        <div class="text-danger small">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            {{ $dateEcheance->diffForHumans() }}
                                        </div>
                                    @else
                                        <div class="text-muted small">
                                            {{ $dateEcheance->diffForHumans() }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-5"><strong>Créée le :</strong></div>
                        <div class="col-7">
                            <div class="small">
                                {{ isset($task->created_at) ? $task->created_at->format('d/m/Y H:i') : 'Date inconnue' }}
                            </div>
                        </div>
                    </div>

                    <div class="row mb-0">
                        <div class="col-5"><strong>Modifiée le :</strong></div>
                        <div class="col-7">
                            <div class="small">
                                {{ isset($task->updated_at) ? $task->updated_at->format('d/m/Y H:i') : 'Date inconnue' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Projet associé -->
            @if(isset($task->projet) && $task->projet)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-semibold">
                            <i class="fas fa-project-diagram me-2"></i>
                            Projet associé
                        </h6>
                    </div>
                    <div class="card-body">
                        <h6 class="mb-1">{{ $task->projet->nom ?? 'Projet sans nom' }}</h6>
                        <p class="text-muted small mb-2">{{ isset($task->projet->description) ? Str::limit($task->projet->description, 80) : 'Aucune description' }}</p>
                        <div class="small mb-2">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            {{ $task->projet->zone_geographique ?? 'Zone non définie' }}
                        </div>
                        @if(isset($task->projet->pourcentage_avancement))
                            <div class="small mb-2">
                                <div class="d-flex justify-content-between">
                                    <span>Avancement global</span>
                                    <span>{{ $task->projet->pourcentage_avancement }}%</span>
                                </div>
                                <div class="progress mt-1" style="height: 4px;">
                                    <div class="progress-bar" style="width: {{ $task->projet->pourcentage_avancement }}%"></div>
                                </div>
                            </div>
                        @endif
                        <a href="{{ route('projects.show', $task->projet->id ?? 0) }}" class="btn btn-sm btn-outline-primary">
                            Voir le projet
                        </a>
                    </div>
                </div>
            @endif

            <!-- Événement associé -->
            @if(isset($task->evenement) && $task->evenement)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-semibold">
                            <i class="fas fa-calendar-alt me-2"></i>
                            Événement associé
                        </h6>
                    </div>
                    <div class="card-body">
                        <h6 class="mb-1">{{ $task->evenement->titre ?? 'Événement sans titre' }}</h6>
                        <div class="small mb-2">
                            <i class="fas fa-clock me-1"></i>
                            @if(isset($task->evenement->date_debut) && isset($task->evenement->date_fin))
                                {{ \Carbon\Carbon::parse($task->evenement->date_debut)->format('d/m/Y H:i') }} -
                                {{ \Carbon\Carbon::parse($task->evenement->date_fin)->format('H:i') }}
                            @else
                                Horaire non défini
                            @endif
                        </div>
                        <div class="small mb-2">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            {{ $task->evenement->lieu ?? 'Lieu non défini' }}
                        </div>
                        <a href="{{ route('events.show', $task->evenement->id ?? 0) }}" class="btn btn-sm btn-outline-success">
                            Voir l'événement
                        </a>
                    </div>
                </div>
            @endif

            <!-- Actions rapides -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-bolt me-2"></i>
                        Actions rapides
                    </h6>
                </div>
                <div class="card-body">
                    @auth
                        @if(auth()->user()->role === 'admin' || (isset($task->id_utilisateur) && auth()->id() === $task->id_utilisateur))
                            @if($statut === 'a_faire')
                                <button type="button" class="btn btn-primary w-100 mb-2" onclick="startTask()">
                                    <i class="fas fa-play me-2"></i>Commencer la tâche
                                </button>
                            @endif

                            @if($statut === 'en_cours')
                                <button type="button" class="btn btn-warning w-100 mb-2" onclick="pauseTask()">
                                    <i class="fas fa-pause me-2"></i>Mettre en pause
                                </button>
                            @endif

                            @if($statut !== 'termine')
                                <button type="button" class="btn btn-success w-100 mb-2" onclick="markAsCompleted()">
                                    <i class="fas fa-check me-2"></i>Marquer terminé
                                </button>
                            @endif
                        @endif
                    @endauth

                    <a href="{{ route('reports.create', ['task_id' => $task->id ?? 0]) }}" class="btn btn-info w-100 mb-2">
                        <i class="fas fa-file-plus me-2"></i>Créer un rapport
                    </a>

                    <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-list me-2"></i>Retour à la liste
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer l'action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="confirmMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="confirmButton">Confirmer</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Fonction pour mettre à jour le statut
function updateTaskStatus(status) {
    const taskId = {{ $task->id ?? 0 }};

    if (!taskId) {
        alert('Erreur: ID de tâche invalide');
        return;
    }

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
            location.reload();
        } else {
            alert('Erreur lors de la mise à jour');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur de connexion');
    });
}

// Actions rapides
function startTask() {
    updateTaskStatus('en_cours');
}

function pauseTask() {
    updateTaskStatus('a_faire');
}

function markAsCompleted() {
    if (confirm('Êtes-vous sûr de vouloir marquer cette tâche comme terminée ?')) {
        updateTaskStatus('termine');
    }
}

// Mise à jour de la progression
function updateProgress(percentage) {
    const taskId = {{ $task->id ?? 0 }};

    if (!taskId) {
        alert('Erreur: ID de tâche invalide');
        return;
    }

    fetch(`/api/tasks/${taskId}/quick-update`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            progression: percentage,
            statut: percentage === 100 ? 'termine' : (percentage > 0 ? 'en_cours' : '{{ $statut }}')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur lors de la mise à jour');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur de connexion');
    });
}

function customProgress() {
    const current = {{ $progression }};
    const newProgress = prompt('Nouvelle progression (%) :', current);

    if (newProgress !== null && !isNaN(newProgress) && newProgress >= 0 && newProgress <= 100) {
        updateProgress(parseInt(newProgress));
    }
}

// Suppression de tâche
function deleteTask() {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette tâche ? Cette action est irréversible.')) {
        const taskId = {{ $task->id ?? 0 }};

        if (!taskId) {
            alert('Erreur: ID de tâche invalide');
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/tasks/${taskId}`;

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        form.appendChild(csrfInput);

        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);

        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
@endsection
