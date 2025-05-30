@extends('layouts.app')

@section('title', 'Nouvelle tâche - PlanifTech ORMVAT')

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
                            <li class="breadcrumb-item active">Nouvelle tâche</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-0 text-dark">
                        <i class="fas fa-plus-circle me-2 text-primary"></i>
                        Créer une nouvelle tâche
                    </h1>
                    <p class="text-muted mb-0">
                        Définissez une nouvelle intervention technique pour l'ORMVAT
                    </p>
                </div>
                <div>
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
            <!-- Formulaire principal -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-edit me-2 text-primary"></i>
                        Informations de la tâche
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tasks.store') }}" class="needs-validation" novalidate>
                        @csrf

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
                                   value="{{ old('titre') }}"
                                   placeholder="Ex: Inspection du canal principal secteur B4"
                                   required
                                   maxlength="100">
                            @error('titre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">
                                    Donnez un titre clair et descriptif à votre tâche
                                </div>
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
                                      placeholder="Décrivez précisément les actions à réaliser, les équipements concernés, les procédures à suivre..."
                                      required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">
                                    Précisez les objectifs, méthodes et équipements nécessaires
                                </div>
                            @enderror
                        </div>

                        <div class="row">
                            <!-- Priorité -->
                            <div class="col-md-6 mb-4">
                                <label for="priorite" class="form-label fw-semibold">
                                    <i class="fas fa-exclamation-circle me-2 text-muted"></i>
                                    Priorité <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('priorite') is-invalid @enderror"
                                        id="priorite"
                                        name="priorite"
                                        required>
                                    <option value="">Sélectionner une priorité</option>
                                    <option value="basse" {{ old('priorite') === 'basse' ? 'selected' : '' }}>
                                        🟢 Basse - Maintenance préventive
                                    </option>
                                    <option value="moyenne" {{ old('priorite') === 'moyenne' ? 'selected' : '' }}>
                                        🟡 Moyenne - Intervention programmée
                                    </option>
                                    <option value="haute" {{ old('priorite') === 'haute' ? 'selected' : '' }}>
                                        🔴 Haute - Intervention urgente
                                    </option>
                                </select>
                                @error('priorite')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Date d'échéance -->
                            <div class="col-md-6 mb-4">
                                <label for="date_echeance" class="form-label fw-semibold">
                                    <i class="fas fa-calendar me-2 text-muted"></i>
                                    Date d'échéance
                                </label>
                                <input type="date"
                                       class="form-control @error('date_echeance') is-invalid @enderror"
                                       id="date_echeance"
                                       name="date_echeance"
                                       value="{{ old('date_echeance') }}"
                                       min="{{ date('Y-m-d') }}">
                                @error('date_echeance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="form-text">
                                        Date limite pour réaliser cette tâche
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Assignation -->
                        <div class="mb-4">
                            <label for="id_utilisateur" class="form-label fw-semibold">
                                <i class="fas fa-user me-2 text-muted"></i>
                                Assigner à <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('id_utilisateur') is-invalid @enderror"
                                    id="id_utilisateur"
                                    name="id_utilisateur"
                                    required>
                                <option value="">Sélectionner un technicien</option>
                                @foreach($users ?? [] as $user)
                                    <option value="{{ $user->id }}"
                                            {{ old('id_utilisateur') == $user->id ? 'selected' : '' }}
                                            data-role="{{ $user->role }}">
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
                            @else
                                <div class="form-text">
                                    Choisissez le technicien responsable de cette tâche
                                </div>
                            @enderror
                        </div>

                        <!-- Projet associé (optionnel) -->
                        <div class="mb-4">
                            <label for="id_projet" class="form-label fw-semibold">
                                <i class="fas fa-project-diagram me-2 text-muted"></i>
                                Projet associé (optionnel)
                            </label>
                            <select class="form-select @error('id_projet') is-invalid @enderror"
                                    id="id_projet"
                                    name="id_projet">
                                <option value="">Aucun projet spécifique</option>
                                @foreach($projects ?? [] as $project)
                                    <option value="{{ $project->id }}"
                                            {{ old('id_projet') == $project->id ? 'selected' : '' }}>
                                        {{ $project->nom }}
                                        ({{ $project->zone_geographique }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_projet')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">
                                    Associez cette tâche à un projet existant si applicable
                                </div>
                            @enderror
                        </div>

                        <!-- Événement associé (optionnel) -->
                        <div class="mb-4">
                            <label for="id_evenement" class="form-label fw-semibold">
                                <i class="fas fa-calendar-alt me-2 text-muted"></i>
                                Événement associé (optionnel)
                            </label>
                            <select class="form-select @error('id_evenement') is-invalid @enderror"
                                    id="id_evenement"
                                    name="id_evenement">
                                <option value="">Aucun événement lié</option>
                                @foreach($events ?? [] as $event)
                                    <option value="{{ $event->id }}"
                                            {{ old('id_evenement') == $event->id ? 'selected' : '' }}>
                                        {{ $event->titre }}
                                        ({{ Carbon\Carbon::parse($event->date_debut)->format('d/m/Y H:i') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_evenement')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">
                                    Liez cette tâche à un événement planifié
                                </div>
                            @enderror
                        </div>

                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>
                                    Créer la tâche
                                </button>
                                <button type="button" class="btn btn-outline-success btn-lg ms-2" onclick="saveAsDraft()">
                                    <i class="fas fa-file-alt me-2"></i>
                                    Sauvegarder comme brouillon
                                </button>
                            </div>
                            <div>
                                <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>
                                    Annuler
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar avec aide -->
        <div class="col-lg-4">
            <!-- Conseils pour créer une tâche -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-lightbulb me-2"></i>
                        Conseils pour une bonne tâche
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="mb-3">
                            <strong>📝 Titre clair</strong>
                            <p class="mb-0 text-muted">Utilisez des termes précis : localisation, équipement, type d'intervention</p>
                        </div>

                        <div class="mb-3">
                            <strong>📋 Description détaillée</strong>
                            <p class="mb-0 text-muted">Incluez : objectifs, méthodes, outils nécessaires, consignes de sécurité</p>
                        </div>

                        <div class="mb-3">
                            <strong>⏰ Priorité adaptée</strong>
                            <ul class="mb-0 text-muted ps-3">
                                <li><strong>Haute :</strong> Urgence, panne critique</li>
                                <li><strong>Moyenne :</strong> Intervention programmée</li>
                                <li><strong>Basse :</strong> Maintenance préventive</li>
                            </ul>
                        </div>

                        <div class="mb-0">
                            <strong>👤 Assignation pertinente</strong>
                            <p class="mb-0 text-muted">Choisissez le technicien selon ses compétences et sa charge de travail</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Exemples de tâches ORMVAT -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-list-ul me-2"></i>
                        Exemples de tâches ORMVAT
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="mb-2">
                            <strong class="text-primary">Maintenance :</strong>
                            <p class="mb-1 text-muted">• Entretien station de pompage P12</p>
                            <p class="mb-3 text-muted">• Nettoyage des canaux secondaires</p>
                        </div>

                        <div class="mb-2">
                            <strong class="text-warning">Inspection :</strong>
                            <p class="mb-1 text-muted">• Contrôle qualité eau d'irrigation</p>
                            <p class="mb-3 text-muted">• Vérification vannes secteur Est</p>
                        </div>

                        <div class="mb-0">
                            <strong class="text-success">Installation :</strong>
                            <p class="mb-1 text-muted">• Pose nouveaux compteurs</p>
                            <p class="mb-0 text-muted">• Installation capteurs automatiques</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistiques rapides -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-chart-bar me-2"></i>
                        Charge de travail actuelle
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($workload ?? [] as $user)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="small">
                                <strong>{{ $user->prenom }} {{ $user->nom }}</strong>
                                <div class="text-muted">{{ ucfirst($user->role) }}</div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-{{ $user->task_count > 5 ? 'danger' : ($user->task_count > 2 ? 'warning' : 'success') }}">
                                    {{ $user->task_count }} tâches
                                </span>
                            </div>
                        </div>
                    @endforeach
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

// Sauvegarde comme brouillon (fonctionnalité future)
function saveAsDraft() {
    // Cette fonctionnalité peut être implémentée plus tard
    alert('Fonctionnalité de brouillon à implémenter');
}

// Auto-complétion et suggestions
document.getElementById('titre').addEventListener('input', function() {
    const value = this.value.toLowerCase();
    const suggestions = [
        'Inspection du canal principal',
        'Maintenance station de pompage',
        'Contrôle qualité eau',
        'Vérification des vannes',
        'Entretien bassins de rétention',
        'Relevé des compteurs',
        'Nettoyage canaux secondaires'
    ];

    // Implémenter auto-complétion si nécessaire
});

// Mise à jour dynamique de la charge de travail
document.getElementById('id_utilisateur').addEventListener('change', function() {
    const userId = this.value;
    if (userId) {
        // Afficher la charge de travail du technicien sélectionné
        highlightUserWorkload(userId);
    }
});

function highlightUserWorkload(userId) {
    // Mettre en évidence la charge de travail dans la sidebar
    // Cette fonction peut être développée pour une UX améliorée
}

// Validation de la date d'échéance
document.getElementById('date_echeance').addEventListener('change', function() {
    const selectedDate = new Date(this.value);
    const today = new Date();

    if (selectedDate < today) {
        this.setCustomValidity('La date d\'échéance ne peut pas être dans le passé');
    } else {
        this.setCustomValidity('');
    }
});

// Adaptation des suggestions selon la priorité
document.getElementById('priorite').addEventListener('change', function() {
    const priority = this.value;
    const descriptionField = document.getElementById('description');

    if (priority === 'haute') {
        if (!descriptionField.value.includes('URGENCE')) {
            descriptionField.placeholder = 'URGENCE - Décrivez la nature de l\'intervention urgente, les risques, et les mesures de sécurité...';
        }
    } else {
        descriptionField.placeholder = 'Décrivez précisément les actions à réaliser, les équipements concernés, les procédures à suivre...';
    }
});
</script>
@endpush
@endsection
