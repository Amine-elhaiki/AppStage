@extends('layouts.app')

@section('title', 'Nouvelle tâche - PlanifTech ORMVAT')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-plus text-primary me-2"></i>
                Nouvelle tâche
            </h1>
            <p class="text-muted mb-0">Créer une nouvelle tâche à assigner à un membre de l'équipe</p>
        </div>
        <div>
            <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                Retour à la liste
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Informations de la tâche
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('tasks.store') }}" method="POST" id="taskForm">
                        @csrf

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="titre" class="form-label fw-semibold">
                                        <i class="fas fa-tag me-1"></i>
                                        Titre de la tâche <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control @error('titre') is-invalid @enderror"
                                           id="titre"
                                           name="titre"
                                           value="{{ old('titre') }}"
                                           placeholder="Ex: Maintenance pompe secteur Nord"
                                           required>
                                    @error('titre')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="priorite" class="form-label fw-semibold">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Priorité <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('priorite') is-invalid @enderror"
                                            id="priorite"
                                            name="priorite"
                                            required>
                                        <option value="">Sélectionner une priorité</option>
                                        <option value="urgente" {{ old('priorite') === 'urgente' ? 'selected' : '' }}>
                                            🔴 Urgente
                                        </option>
                                        <option value="haute" {{ old('priorite') === 'haute' ? 'selected' : '' }}>
                                            🟠 Haute
                                        </option>
                                        <option value="normale" {{ old('priorite') === 'normale' ? 'selected' : '' }} selected>
                                            🟡 Normale
                                        </option>
                                        <option value="basse" {{ old('priorite') === 'basse' ? 'selected' : '' }}>
                                            🟢 Basse
                                        </option>
                                    </select>
                                    @error('priorite')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-semibold">
                                <i class="fas fa-align-left me-1"></i>
                                Description détaillée <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description"
                                      name="description"
                                      rows="4"
                                      placeholder="Décrivez en détail la tâche à effectuer, les objectifs, les contraintes..."
                                      required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-lightbulb me-1"></i>
                                Plus la description est détaillée, plus il sera facile pour le technicien de réaliser la tâche.
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="user_id" class="form-label fw-semibold">
                                        <i class="fas fa-user me-1"></i>
                                        Assigner à <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('user_id') is-invalid @enderror"
                                            id="user_id"
                                            name="user_id"
                                            required>
                                        <option value="">Choisir un utilisateur</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}"
                                                    {{ old('user_id') == $user->id ? 'selected' : '' }}
                                                    data-specialite="{{ $user->specialite }}"
                                                    data-role="{{ $user->role }}">
                                                {{ $user->prenom }} {{ $user->nom }}
                                                ({{ ucfirst($user->role) }})
                                                @if($user->specialite)
                                                    - {{ $user->specialite }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_echeance" class="form-label fw-semibold">
                                        <i class="fas fa-calendar-alt me-1"></i>
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
                                    @enderror
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Laissez vide si aucune échéance spécifique
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="project_id" class="form-label fw-semibold">
                                <i class="fas fa-project-diagram me-1"></i>
                                Projet associé
                            </label>
                            <select class="form-select @error('project_id') is-invalid @enderror"
                                    id="project_id"
                                    name="project_id">
                                <option value="">Aucun projet (tâche autonome)</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}"
                                            {{ old('project_id') == $project->id ? 'selected' : '' }}
                                            data-description="{{ $project->description }}"
                                            data-zone="{{ $project->zone_geographique }}">
                                        {{ $project->nom }} ({{ $project->zone_geographique }})
                                    </option>
                                @endforeach
                            </select>
                            @error('project_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-link me-1"></i>
                                Associer cette tâche à un projet existant pour un meilleur suivi
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="commentaires" class="form-label fw-semibold">
                                <i class="fas fa-comments me-1"></i>
                                Commentaires supplémentaires
                            </label>
                            <textarea class="form-control @error('commentaires') is-invalid @enderror"
                                      id="commentaires"
                                      name="commentaires"
                                      rows="3"
                                      placeholder="Instructions particulières, matériel nécessaire, précautions à prendre...">{{ old('commentaires') }}</textarea>
                            @error('commentaires')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Créer la tâche
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar avec informations utiles -->
        <div class="col-lg-4">
            <!-- Aide à la création -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-question-circle me-2"></i>
                        Aide à la création
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="fw-semibold">🎯 Titre efficace</h6>
                        <small class="text-muted">
                            Utilisez un titre clair et précis (ex: "Maintenance pompe P-125", "Inspection canal C-45")
                        </small>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-semibold">📝 Description détaillée</h6>
                        <small class="text-muted">
                            Incluez: objectif, procédure, matériel nécessaire, mesures de sécurité
                        </small>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-semibold">⚡ Niveaux de priorité</h6>
                        <ul class="list-unstyled small">
                            <li><span class="text-danger">🔴 Urgente:</span> Intervention immédiate</li>
                            <li><span class="text-warning">🟠 Haute:</span> À traiter rapidement</li>
                            <li><span class="text-info">🟡 Normale:</span> Planning habituel</li>
                            <li><span class="text-success">🟢 Basse:</span> Quand possible</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Statistiques équipe -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>
                        Charge de travail équipe
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($users->take(5) as $user)
                        @php
                            $activeTasks = $user->tasks()->whereIn('statut', ['a_faire', 'en_cours'])->count();
                            $maxTasks = 10; // Limite arbitraire pour visualisation
                            $percentage = min(($activeTasks / $maxTasks) * 100, 100);
                            $colorClass = $percentage < 50 ? 'success' : ($percentage < 80 ? 'warning' : 'danger');
                        @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-semibold">{{ $user->prenom }} {{ $user->nom }}</span>
                                <span class="badge bg-{{ $colorClass }}">{{ $activeTasks }} tâches</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-{{ $colorClass }}"
                                     role="progressbar"
                                     style="width: {{ $percentage }}%"></div>
                            </div>
                            @if($user->specialite)
                                <small class="text-muted">{{ $user->specialite }}</small>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Projets actifs -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-project-diagram me-2"></i>
                        Projets actifs
                    </h6>
                </div>
                <div class="card-body">
                    @forelse($projects->take(5) as $project)
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small fw-semibold">{{ Str::limit($project->nom, 25) }}</span>
                                <span class="badge bg-{{ $project->status_color }}">{{ $project->pourcentage_avancement }}%</span>
                            </div>
                            <small class="text-muted">{{ $project->zone_geographique }}</small>
                        </div>
                    @empty
                        <small class="text-muted">Aucun projet actif</small>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation côté client
    const form = document.getElementById('taskForm');
    const titreInput = document.getElementById('titre');
    const descriptionInput = document.getElementById('description');
    const userSelect = document.getElementById('user_id');
    const projectSelect = document.getElementById('project_id');

    // Validation en temps réel
    titreInput.addEventListener('input', function() {
        if (this.value.length < 5) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });

    descriptionInput.addEventListener('input', function() {
        if (this.value.length < 20) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });

    // Affichage des informations utilisateur sélectionné
    userSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const specialite = selectedOption.dataset.specialite;
            const role = selectedOption.dataset.role;

            // Afficher un tooltip ou une information
            console.log(`Utilisateur sélectionné: ${role}, Spécialité: ${specialite}`);
        }
    });

    // Affichage des informations projet sélectionné
    projectSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const description = selectedOption.dataset.description;
            const zone = selectedOption.dataset.zone;

            console.log(`Projet sélectionné - Zone: ${zone}`);
        }
    });

    // Suggestions automatiques pour les échéances selon la priorité
    document.getElementById('priorite').addEventListener('change', function() {
        const dateEcheance = document.getElementById('date_echeance');
        const today = new Date();
        let suggestedDate = new Date(today);

        switch(this.value) {
            case 'urgente':
                suggestedDate.setDate(today.getDate() + 1); // Demain
                break;
            case 'haute':
                suggestedDate.setDate(today.getDate() + 3); // Dans 3 jours
                break;
            case 'normale':
                suggestedDate.setDate(today.getDate() + 7); // Dans 1 semaine
                break;
            case 'basse':
                suggestedDate.setDate(today.getDate() + 14); // Dans 2 semaines
                break;
        }

        if (this.value && !dateEcheance.value) {
            dateEcheance.value = suggestedDate.toISOString().split('T')[0];
        }
    });

    // Animation de soumission
    form.addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Création en cours...';
        submitBtn.disabled = true;
    });

    // Auto-save en local storage
    const inputs = form.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        // Charger depuis localStorage
        const saved = localStorage.getItem(`task_create_${input.name}`);
        if (saved && !input.value) {
            input.value = saved;
        }

        // Sauvegarder dans localStorage
        input.addEventListener('input', function() {
            localStorage.setItem(`task_create_${this.name}`, this.value);
        });
    });

    // Nettoyer localStorage après soumission réussie
    if (window.location.search.includes('success')) {
        inputs.forEach(input => {
            localStorage.removeItem(`task_create_${input.name}`);
        });
    }
});

// Raccourcis clavier
document.addEventListener('keydown', function(e) {
    // Ctrl + S pour sauvegarder
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        document.getElementById('taskForm').submit();
    }

    // Escape pour annuler
    if (e.key === 'Escape') {
        if (confirm('Êtes-vous sûr de vouloir annuler ? Les modifications non sauvegardées seront perdues.')) {
            window.location.href = "{{ route('tasks.index') }}";
        }
    }
});
</script>
@endpush

@push('styles')
<style>
.form-control:focus, .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
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

.card-header {
    border-radius: 12px 12px 0 0;
}

.badge {
    font-size: 0.75em;
}

.form-text {
    font-size: 0.875em;
}
</style>
@endpush
@endsection
