@extends('layouts.app')

@section('title', 'Modifier la tâche - PlanifTech ORMVAT')

@section('content')
<div class="container-fluid py-4">
    <!-- En-tête -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}">Tableau de bord</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('tasks.index') }}">Tâches</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('tasks.show', $task->id) }}">{{ Str::limit($task->titre, 30) }}</a>
                            </li>
                            <li class="breadcrumb-item active">Modifier</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-0 text-dark">
                        <i class="fas fa-edit me-2 text-primary"></i>
                        Modifier la tâche
                    </h1>
                    <p class="text-muted mb-0">
                        Mettez à jour les informations de cette intervention technique
                    </p>
                </div>
                <div>
                    <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-eye me-2"></i>
                        Voir la tâche
                    </a>
                    <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Retour à la liste
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Informations actuelles -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light border-bottom-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-semibold">
                            <i class="fas fa-info-circle me-2 text-info"></i>
                            État actuel de la tâche
                        </h6>
                        <div>
                            <span class="badge bg-{{ $task->statut === 'termine' ? 'success' : ($task->statut === 'en_cours' ? 'primary' : 'secondary') }} me-2">
                                {{ ucfirst(str_replace('_', ' ', $task->statut)) }}
                            </span>
                            <span class="badge bg-{{ $task->priorite === 'haute' ? 'danger' : ($task->priorite === 'moyenne' ? 'warning' : 'secondary') }}">
                                Priorité {{ ucfirst($task->priorite) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">Créée le</small>
                            <div class="fw-semibold">{{ $task->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Dernière modification</small>
                            <div class="fw-semibold">{{ $task->updated_at->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                    @if($task->progression > 0)
                        <div class="mt-3">
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">Progression actuelle</small>
                                <small class="text-muted">{{ $task->progression }}%</small>
                            </div>
                            <div class="progress mt-1" style="height: 6px;">
                                <div class="progress-bar" role="progressbar" style="width: {{ $task->progression }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Formulaire de modification -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-edit me-2 text-primary"></i>
                        Modifier les informations
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tasks.update', $task->id) }}" class="needs-validation" novalidate>
                        @csrf
                        @method('PUT')

                        <!-- Titre -->
                        <div class="mb-4">
                            <label for="titre" class="form-label fw-semibold">
                                <i class="fas fa-heading me-2 text-muted"></i>
                                Titre de la tâche <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control form-control-lg @error('titre') is-invalid @enderror"
                                   id="titre"
                                   name="titre"
                                   value="{{ old('titre', $task->titre) }}"
                                   placeholder="Ex: Inspection du canal principal secteur B4"
                                   required
                                   maxlength="100">
                            @error('titre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description" class="form-label fw-semibold">
                                <i class="fas fa-align-left me-2 text-muted"></i>
                                Description détaillée <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description"
                                      name="description"
                                      rows="4"
                                      placeholder="Décrivez précisément les actions à réaliser..."
                                      required>{{ old('description', $task->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <!-- Statut -->
                            <div class="col-md-4 mb-4">
                                <label for="statut" class="form-label fw-semibold">
                                    <i class="fas fa-flag me-2 text-muted"></i>
                                    Statut <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('statut') is-invalid @enderror"
                                        id="statut"
                                        name="statut"
                                        required>
                                    <option value="a_faire" {{ old('statut', $task->statut) === 'a_faire' ? 'selected' : '' }}>
                                        📋 À faire
                                    </option>
                                    <option value="en_cours" {{ old('statut', $task->statut) === 'en_cours' ? 'selected' : '' }}>
                                        🔄 En cours
                                    </option>
                                    <option value="termine" {{ old('statut', $task->statut) === 'termine' ? 'selected' : '' }}>
                                        ✅ Terminé
                                    </option>
                                </select>
                                @error('statut')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Priorité -->
                            <div class="col-md-4 mb-4">
                                <label for="priorite" class="form-label fw-semibold">
                                    <i class="fas fa-exclamation-circle me-2 text-muted"></i>
                                    Priorité <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('priorite') is-invalid @enderror"
                                        id="priorite"
                                        name="priorite"
                                        required>
                                    <option value="basse" {{ old('priorite', $task->priorite) === 'basse' ? 'selected' : '' }}>
                                        🟢 Basse
                                    </option>
                                    <option value="moyenne" {{ old('priorite', $task->priorite) === 'moyenne' ? 'selected' : '' }}>
                                        🟡 Moyenne
                                    </option>
                                    <option value="haute" {{ old('priorite', $task->priorite) === 'haute' ? 'selected' : '' }}>
                                        🔴 Haute
                                    </option>
                                </select>
                                @error('priorite')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Progression -->
                            <div class="col-md-4 mb-4">
                                <label for="progression" class="form-label fw-semibold">
                                    <i class="fas fa-chart-line me-2 text-muted"></i>
                                    Progression
                                </label>
                                <div class="input-group">
                                    <input type="number"
                                           class="form-control @error('progression') is-invalid @enderror"
                                           id="progression"
                                           name="progression"
                                           value="{{ old('progression', $task->progression ?? 0) }}"
                                           min="0"
                                           max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                                @error('progression')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Date d'échéance -->
                        <div class="mb-4">
                            <label for="date_echeance" class="form-label fw-semibold">
                                <i class="fas fa-calendar me-2 text-muted"></i>
                                Date d'échéance
                            </label>
                            <input type="date"
                                   class="form-control @error('date_echeance') is-invalid @enderror"
                                   id="date_echeance"
                                   name="date_echeance"
                                   value="{{ old('date_echeance', $task->date_echeance ? $task->date_echeance->format('Y-m-d') : '') }}">
                            @error('date_echeance')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                @if($task->date_echeance && $task->date_echeance->isPast() && $task->statut !== 'termine')
                                    <div class="form-text text-danger">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Cette tâche est en retard depuis le {{ $task->date_echeance->format('d/m/Y') }}
                                    </div>
                                @endif
                            @enderror
                        </div>

                        @if(Auth::user()->role === 'admin')
                        <!-- Assignation (admin uniquement) -->
                        <div class="mb-4">
                            <label for="id_utilisateur" class="form-label fw-semibold">
                                <i class="fas fa-user me-2 text-muted"></i>
                                Réassigner à <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('id_utilisateur') is-invalid @enderror"
                                    id="id_utilisateur"
                                    name="id_utilisateur"
                                    required>
                                @foreach($users ?? [] as $user)
                                    <option value="{{ $user->id }}"
                                            {{ old('id_utilisateur', $task->id_utilisateur) == $user->id ? 'selected' : '' }}>
                                        {{ $user->prenom }} {{ $user->nom }}
                                        ({{ ucfirst($user->role) }})
                                        @if($user->specialite)
                                            - {{ $user->specialite }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('id_utilisateur')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if(old('id_utilisateur', $task->id_utilisateur) != $task->id_utilisateur)
                                <div class="form-text text-warning">
                                    <i class="fas fa-info-circle"></i>
                                    La réassignation enverra une notification au nouveau technicien
                                </div>
                            @endif
                        </div>

                        <!-- Projet associé -->
                        <div class="mb-4">
                            <label for="id_projet" class="form-label fw-semibold">
                                <i class="fas fa-project-diagram me-2 text-muted"></i>
                                Projet associé
                            </label>
                            <select class="form-select @error('id_projet') is-invalid @enderror"
                                    id="id_projet"
                                    name="id_projet">
                                <option value="">Aucun projet spécifique</option>
                                @foreach($projects ?? [] as $project)
                                    <option value="{{ $project->id }}"
                                            {{ old('id_projet', $task->id_projet) == $project->id ? 'selected' : '' }}>
                                        {{ $project->nom }}
                                        ({{ $project->zone_geographique }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_projet')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Événement associé -->
                        <div class="mb-4">
                            <label for="id_evenement" class="form-label fw-semibold">
                                <i class="fas fa-calendar-alt me-2 text-muted"></i>
                                Événement associé
                            </label>
                            <select class="form-select @error('id_evenement') is-invalid @enderror"
                                    id="id_evenement"
                                    name="id_evenement">
                                <option value="">Aucun événement lié</option>
                                @foreach($events ?? [] as $event)
                                    <option value="{{ $event->id }}"
                                            {{ old('id_evenement', $task->id_evenement) == $event->id ? 'selected' : '' }}>
                                        {{ $event->titre }}
                                        ({{ Carbon\Carbon::parse($event->date_debut)->format('d/m/Y H:i') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_evenement')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif

                        <!-- Commentaire de modification -->
                        <div class="mb-4">
                            <label for="commentaire_modification" class="form-label fw-semibold">
                                <i class="fas fa-comment me-2 text-muted"></i>
                                Commentaire sur les modifications (optionnel)
                            </label>
                            <textarea class="form-control"
                                      id="commentaire_modification"
                                      name="commentaire_modification"
                                      rows="2"
                                      placeholder="Expliquez les raisons de ces modifications...">{{ old('commentaire_modification') }}</textarea>
                            <div class="form-text">
                                Ce commentaire sera enregistré dans l'historique des modifications
                            </div>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>
                                    Enregistrer les modifications
                                </button>
                                @if($task->statut !== 'termine' && (Auth::user()->role === 'admin' || Auth::user()->id === $task->id_utilisateur))
                                    <button type="button" class="btn btn-success btn-lg ms-2" onclick="markAsCompleted()">
                                        <i class="fas fa-check me-2"></i>
                                        Marquer comme terminé
                                    </button>
                                @endif
                            </div>
                            <div>
                                <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>
                                    Annuler
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Historique des modifications -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-history me-2"></i>
                        Historique des modifications
                    </h6>
                </div>
                <div class="card-body">
                    @forelse($task->modifications ?? [] as $modification)
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <div class="rounded-circle bg-primary bg-opacity-10 p-2">
                                    <i class="fas fa-edit text-primary"></i>
                                </div>
                            </div>
                            <div class="ms-3">
                                <div class="small">
                                    <strong>{{ $modification->user->prenom }} {{ $modification->user->nom }}</strong>
                                    <div class="text-muted">{{ $modification->created_at->format('d/m/Y H:i') }}</div>
                                </div>
                                <p class="small mb-0">{{ $modification->commentaire }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-3 text-muted">
                            <i class="fas fa-clock fa-2x mb-2"></i>
                            <p class="mb-0 small">Aucune modification enregistrée</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-bolt me-2"></i>
                        Actions rapides
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($task->statut !== 'termine')
                            <button type="button" class="btn btn-outline-success" onclick="quickUpdateStatus('en_cours')">
                                <i class="fas fa-play me-2"></i>Démarrer la tâche
                            </button>
                            <button type="button" class="btn btn-outline-warning" onclick="quickUpdateProgress()">
                                <i class="fas fa-chart-line me-2"></i>Mettre à jour la progression
                            </button>
                        @endif

                        <a href="{{ route('reports.create', ['task_id' => $task->id]) }}" class="btn btn-outline-info">
                            <i class="fas fa-file-plus me-2"></i>Créer un rapport
                        </a>

                        @if(Auth::user()->role === 'admin')
                            <button type="button" class="btn btn-outline-primary" onclick="duplicateTask()">
                                <i class="fas fa-copy me-2"></i>Dupliquer la tâche
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Informations techniques -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-info me-2"></i>
                        Informations techniques
                    </h6>
                </div>
                <div class="card-body small">
                    <div class="row">
                        <div class="col-6">
                            <strong>ID :</strong> #{{ $task->id }}
                        </div>
                        <div class="col-6">
                            <strong>Version :</strong> {{ $task->version ?? 1 }}
                        </div>
                    </div>
                    <hr class="my-2">
                    <div class="text-muted">
                        <div><strong>Créée par :</strong> {{ $task->creator->prenom ?? 'Système' }} {{ $task->creator->nom ?? '' }}</div>
                        <div><strong>Dernière modif :</strong> {{ $task->updated_at->diffForHumans() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Validation du formulaire
(function() {
    'use strict';
    window.addEventListener('load', function() {
        const forms = document.getElementsByClassName('needs-validation');
        Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();

// Marquer comme terminé
function markAsCompleted() {
    if (confirm('Êtes-vous sûr de vouloir marquer cette tâche comme terminée ?')) {
        document.getElementById('statut').value = 'termine';
        document.getElementById('progression').value = '100';
        document.querySelector('form').submit();
    }
}

// Mise à jour rapide du statut
function quickUpdateStatus(status) {
    document.getElementById('statut').value = status;
    if (status === 'en_cours' && document.getElementById('progression').value == '0') {
        document.getElementById('progression').value = '10';
    }
}

// Mise à jour rapide de la progression
function quickUpdateProgress() {
    const currentProgress = parseInt(document.getElementById('progression').value) || 0;
    const newProgress = prompt('Nouvelle progression (%) :', currentProgress);

    if (newProgress !== null && !isNaN(newProgress) && newProgress >= 0 && newProgress <= 100) {
        document.getElementById('progression').value = newProgress;

        if (newProgress == 100) {
            document.getElementById('statut').value = 'termine';
        } else if (newProgress > 0 && document.getElementById('statut').value === 'a_faire') {
            document.getElementById('statut').value = 'en_cours';
        }
    }
}

// Dupliquer la tâche
function duplicateTask() {
    if (confirm('Créer une nouvelle tâche basée sur celle-ci ?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("tasks.store") }}';

        // Ajouter CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);

        // Copier les valeurs actuelles
        const fieldsToCapy = ['titre', 'description', 'priorite', 'id_utilisateur', 'id_projet'];
        fieldsToCapy.forEach(fieldName => {
            const originalField = document.getElementById(fieldName);
            if (originalField) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = fieldName;
                input.value = fieldName === 'titre' ? 'Copie de ' + originalField.value : originalField.value;
                form.appendChild(input);
            }
        });

        document.body.appendChild(form);
        form.submit();
    }
}

// Auto-sauvegarde (toutes les 2 minutes si des modifications)
let hasUnsavedChanges = false;
let originalFormData = new FormData(document.querySelector('form'));

document.querySelector('form').addEventListener('input', function() {
    hasUnsavedChanges = true;
});

// Avertir avant de quitter si modifications non sauvegardées
window.addEventListener('beforeunload', function(e) {
    if (hasUnsavedChanges) {
        e.preventDefault();
        e.returnValue = 'Vous avez des modifications non sauvegardées. Êtes-vous sûr de vouloir quitter ?';
    }
});

// Réinitialiser le flag après soumission
document.querySelector('form').addEventListener('submit', function() {
    hasUnsavedChanges = false;
});
</script>
@endpush
@endsection
