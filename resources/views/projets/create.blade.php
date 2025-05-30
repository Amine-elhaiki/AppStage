@extends('layouts.app')

@section('title', 'Nouveau projet - PlanifTech ORMVAT')

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
                                <a href="{{ route('projects.index') }}">Projets</a>
                            </li>
                            <li class="breadcrumb-item active">Nouveau projet</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-0 text-dark">
                        <i class="fas fa-plus-circle me-2 text-primary"></i>
                        Créer un nouveau projet
                    </h1>
                    <p class="text-muted mb-0">
                        Définissez un nouveau projet hydraulique ou agricole pour l'ORMVAT
                    </p>
                </div>
                <div>
                    <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">
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
                        Informations du projet
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('projects.store') }}" class="needs-validation" novalidate>
                        @csrf

                        <!-- Nom du projet -->
                        <div class="mb-4">
                            <label for="nom" class="form-label fw-semibold">
                                <i class="fas fa-project-diagram me-2 text-muted"></i>
                                Nom du projet <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control form-control-lg @error('nom') is-invalid @enderror"
                                   id="nom"
                                   name="nom"
                                   value="{{ old('nom') }}"
                                   placeholder="Ex: Modernisation du réseau d'irrigation secteur Tadla-Nord"
                                   required
                                   maxlength="100">
                            @error('nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">
                                    Choisissez un nom explicite et unique pour votre projet
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
                                      placeholder="Décrivez les objectifs, la portée, les bénéficiaires et les résultats attendus du projet..."
                                      required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">
                                    Incluez les objectifs, la méthodologie et les résultats attendus
                                </div>
                            @enderror
                        </div>

                        <div class="row">
                            <!-- Date de début -->
                            <div class="col-md-6 mb-4">
                                <label for="date_debut" class="form-label fw-semibold">
                                    <i class="fas fa-calendar-plus me-2 text-muted"></i>
                                    Date de début <span class="text-danger">*</span>
                                </label>
                                <input type="date"
                                       class="form-control @error('date_debut') is-invalid @enderror"
                                       id="date_debut"
                                       name="date_debut"
                                       value="{{ old('date_debut', date('Y-m-d')) }}"
                                       required>
                                @error('date_debut')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Date de fin prévue -->
                            <div class="col-md-6 mb-4">
                                <label for="date_fin" class="form-label fw-semibold">
                                    <i class="fas fa-calendar-check me-2 text-muted"></i>
                                    Date de fin prévue <span class="text-danger">*</span>
                                </label>
                                <input type="date"
                                       class="form-control @error('date_fin') is-invalid @enderror"
                                       id="date_fin"
                                       name="date_fin"
                                       value="{{ old('date_fin') }}"
                                       required>
                                @error('date_fin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="form-text">
                                        Date limite pour la réalisation complète du projet
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Zone géographique -->
                        <div class="mb-4">
                            <label for="zone_geographique" class="form-label fw-semibold">
                                <i class="fas fa-map-marker-alt me-2 text-muted"></i>
                                Zone géographique <span class="text-danger">*</span>
                            </label>
                            <div class="row">
                                <div class="col-md-6">
                                    <select class="form-select @error('zone_geographique') is-invalid @enderror"
                                            id="zone_geographique"
                                            name="zone_geographique"
                                            required>
                                        <option value="">Sélectionner une zone</option>
                                        <optgroup label="Zones principales ORMVAT">
                                            <option value="Tadla-Nord" {{ old('zone_geographique') === 'Tadla-Nord' ? 'selected' : '' }}>Tadla-Nord</option>
                                            <option value="Tadla-Sud" {{ old('zone_geographique') === 'Tadla-Sud' ? 'selected' : '' }}>Tadla-Sud</option>
                                            <option value="Fkih Ben Salah" {{ old('zone_geographique') === 'Fkih Ben Salah' ? 'selected' : '' }}>Fkih Ben Salah</option>
                                            <option value="Béni Mellal" {{ old('zone_geographique') === 'Béni Mellal' ? 'selected' : '' }}>Béni Mellal</option>
                                            <option value="Kasba Tadla" {{ old('zone_geographique') === 'Kasba Tadla' ? 'selected' : '' }}>Kasba Tadla</option>
                                        </optgroup>
                                        <optgroup label="Secteurs spécialisés">
                                            <option value="Périmètres irrigués" {{ old('zone_geographique') === 'Périmètres irrigués' ? 'selected' : '' }}>Périmètres irrigués</option>
                                            <option value="Canaux principaux" {{ old('zone_geographique') === 'Canaux principaux' ? 'selected' : '' }}>Canaux principaux</option>
                                            <option value="Stations de pompage" {{ old('zone_geographique') === 'Stations de pompage' ? 'selected' : '' }}>Stations de pompage</option>
                                            <option value="Réseau de drainage" {{ old('zone_geographique') === 'Réseau de drainage' ? 'selected' : '' }}>Réseau de drainage</option>
                                        </optgroup>
                                        <option value="Autre" {{ old('zone_geographique') === 'Autre' ? 'selected' : '' }}>Autre (préciser)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <input type="text"
                                           class="form-control"
                                           id="zone_autre"
                                           name="zone_autre"
                                           value="{{ old('zone_autre') }}"
                                           placeholder="Précisez si 'Autre' sélectionné"
                                           style="display: none;">
                                </div>
                            </div>
                            @error('zone_geographique')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Responsable du projet -->
                        <div class="mb-4">
                            <label for="id_responsable" class="form-label fw-semibold">
                                <i class="fas fa-user-tie me-2 text-muted"></i>
                                Responsable du projet <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('id_responsable') is-invalid @enderror"
                                    id="id_responsable"
                                    name="id_responsable"
                                    required>
                                <option value="">Sélectionner un responsable</option>
                                @foreach($users ?? [] as $user)
                                    <option value="{{ $user->id }}"
                                            {{ old('id_responsable') == $user->id ? 'selected' : '' }}
                                            data-role="{{ $user->role }}"
                                            data-workload="{{ $user->projets_count ?? 0 }}">
                                        {{ $user->prenom }} {{ $user->nom }}
                                        ({{ ucfirst($user->role) }})
                                        @if($user->specialite)
                                            - {{ $user->specialite }}
                                        @endif
                                        - {{ $user->projets_count ?? 0 }} projet(s) en cours
                                    </option>
                                @endforeach
                            </select>
                            @error('id_responsable')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">
                                    Le responsable supervisera l'ensemble du projet et ses équipes
                                </div>
                            @enderror
                        </div>

                        <!-- Budget prévisionnel (optionnel) -->
                        <div class="mb-4">
                            <label for="budget" class="form-label fw-semibold">
                                <i class="fas fa-coins me-2 text-muted"></i>
                                Budget prévisionnel (optionnel)
                            </label>
                            <div class="input-group">
                                <input type="number"
                                       class="form-control @error('budget') is-invalid @enderror"
                                       id="budget"
                                       name="budget"
                                       value="{{ old('budget') }}"
                                       min="0"
                                       step="1000"
                                       placeholder="0">
                                <span class="input-group-text">MAD</span>
                            </div>
                            @error('budget')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">
                                    Budget prévisionnel en dirhams marocains (estimation)
                                </div>
                            @enderror
                        </div>

                        <!-- Objectifs spécifiques -->
                        <div class="mb-4">
                            <label for="objectifs" class="form-label fw-semibold">
                                <i class="fas fa-bullseye me-2 text-muted"></i>
                                Objectifs spécifiques (optionnel)
                            </label>
                            <textarea class="form-control @error('objectifs') is-invalid @enderror"
                                      id="objectifs"
                                      name="objectifs"
                                      rows="3"
                                      placeholder="Listez les objectifs SMART du projet : Spécifiques, Mesurables, Atteignables, Réalistes, Temporels...">{{ old('objectifs') }}</textarea>
                            @error('objectifs')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">
                                    Définissez des objectifs SMART pour faciliter le suivi du projet
                                </div>
                            @enderror
                        </div>

                        <!-- Bénéficiaires -->
                        <div class="mb-4">
                            <label for="beneficiaires" class="form-label fw-semibold">
                                <i class="fas fa-users me-2 text-muted"></i>
                                Bénéficiaires du projet
                            </label>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="number"
                                           class="form-control @error('nb_beneficiaires') is-invalid @enderror"
                                           id="nb_beneficiaires"
                                           name="nb_beneficiaires"
                                           value="{{ old('nb_beneficiaires') }}"
                                           min="0"
                                           placeholder="Nombre d'agriculteurs">
                                    <div class="form-text">Nombre d'agriculteurs bénéficiaires</div>
                                </div>
                                <div class="col-md-6">
                                    <input type="number"
                                           class="form-control @error('superficie_ha') is-invalid @enderror"
                                           id="superficie_ha"
                                           name="superficie_ha"
                                           value="{{ old('superficie_ha') }}"
                                           min="0"
                                           step="0.1"
                                           placeholder="Superficie en hectares">
                                    <div class="form-text">Superficie concernée (ha)</div>
                                </div>
                            </div>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>
                                    Créer le projet
                                </button>
                                <button type="button" class="btn btn-outline-success btn-lg ms-2" onclick="saveAsDraft()">
                                    <i class="fas fa-file-alt me-2"></i>
                                    Brouillon
                                </button>
                            </div>
                            <div>
                                <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">
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
            <!-- Guide de création -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-compass me-2"></i>
                        Guide de création de projet ORMVAT
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="mb-3">
                            <strong class="text-primary">1. Identification</strong>
                            <p class="mb-0 text-muted">Définissez clairement le nom, la portée et les objectifs du projet</p>
                        </div>

                        <div class="mb-3">
                            <strong class="text-primary">2. Planification</strong>
                            <p class="mb-0 text-muted">Établissez un calendrier réaliste avec des étapes intermédiaires</p>
                        </div>

                        <div class="mb-3">
                            <strong class="text-primary">3. Ressources</strong>
                            <p class="mb-0 text-muted">Assignez un responsable compétent et estimez le budget nécessaire</p>
                        </div>

                        <div class="mb-0">
                            <strong class="text-primary">4. Impact</strong>
                            <p class="mb-0 text-muted">Identifiez les bénéficiaires et les retombées attendues</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Types de projets ORMVAT -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-layer-group me-2"></i>
                        Types de projets ORMVAT
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="mb-2">
                            <strong class="text-info">Infrastructure :</strong>
                            <p class="mb-1 text-muted">• Modernisation des canaux</p>
                            <p class="mb-3 text-muted">• Réhabilitation des stations</p>
                        </div>

                        <div class="mb-2">
                            <strong class="text-warning">Développement :</strong>
                            <p class="mb-1 text-muted">• Extension des périmètres</p>
                            <p class="mb-3 text-muted">• Nouvelles zones irriguées</p>
                        </div>

                        <div class="mb-0">
                            <strong class="text-success">Innovation :</strong>
                            <p class="mb-1 text-muted">• Irrigation intelligente</p>
                            <p class="mb-0 text-muted">• Monitoring automatisé</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Indicateurs clés -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-chart-line me-2"></i>
                        Projets actifs - Statistiques
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="h5 mb-0 text-primary">{{ $stats['total_active'] ?? 0 }}</div>
                            <small class="text-muted">Projets actifs</small>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="h5 mb-0 text-success">{{ $stats['completed_this_year'] ?? 0 }}</div>
                            <small class="text-muted">Terminés cette année</small>
                        </div>
                        <div class="col-6">
                            <div class="h5 mb-0 text-warning">{{ $stats['avg_duration'] ?? 0 }}</div>
                            <small class="text-muted">Durée moyenne (mois)</small>
                        </div>
                        <div class="col-6">
                            <div class="h5 mb-0 text-info">{{ $stats['total_budget'] ?? 0 }}M</div>
                            <small class="text-muted">Budget total (MAD)</small>
                        </div>
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

// Gestion du champ "Autre" pour la zone géographique
document.getElementById('zone_geographique').addEventListener('change', function() {
    const autreField = document.getElementById('zone_autre');
    if (this.value === 'Autre') {
        autreField.style.display = 'block';
        autreField.required = true;
    } else {
        autreField.style.display = 'none';
        autreField.required = false;
        autreField.value = '';
    }
});

// Validation des dates
document.getElementById('date_debut').addEventListener('change', validateDates);
document.getElementById('date_fin').addEventListener('change', validateDates);

function validateDates() {
    const dateDebut = new Date(document.getElementById('date_debut').value);
    const dateFin = new Date(document.getElementById('date_fin').value);
    const dateFinField = document.getElementById('date_fin');

    if (dateDebut && dateFin && dateFin <= dateDebut) {
        dateFinField.setCustomValidity('La date de fin doit être postérieure à la date de début');
    } else {
        dateFinField.setCustomValidity('');
    }
}

// Affichage de la charge de travail du responsable sélectionné
document.getElementById('id_responsable').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
        const workload = selectedOption.dataset.workload;
        const role = selectedOption.dataset.role;

        // Afficher un indicateur visuel de la charge
        if (workload > 3) {
            this.classList.add('border-warning');
            // Ajouter un message d'avertissement si nécessaire
        } else {
            this.classList.remove('border-warning');
        }
    }
});

// Calcul automatique de la durée du projet
function calculateProjectDuration() {
    const dateDebut = new Date(document.getElementById('date_debut').value);
    const dateFin = new Date(document.getElementById('date_fin').value);

    if (dateDebut && dateFin && dateFin > dateDebut) {
        const diffTime = Math.abs(dateFin - dateDebut);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        const diffMonths = Math.round(diffDays / 30.44);

        // Afficher la durée calculée (peut être ajouté dans l'interface)
        console.log(`Durée du projet: ${diffDays} jours (environ ${diffMonths} mois)`);
    }
}

// Sauvegarde comme brouillon (fonctionnalité future)
function saveAsDraft() {
    alert('Fonctionnalité de brouillon à implémenter');
}

// Auto-complétion pour le nom du projet
const projectNames = [
    'Modernisation du réseau d\'irrigation',
    'Réhabilitation des canaux principaux',
    'Extension du périmètre irrigué',
    'Amélioration de la qualité de l\'eau',
    'Installation de nouveaux équipements',
    'Développement de l\'irrigation localisée'
];

document.getElementById('nom').addEventListener('input', function() {
    // Implémenter auto-complétion si nécessaire
});

// Validation du budget
document.getElementById('budget').addEventListener('input', function() {
    const value = parseFloat(this.value);
    if (value < 0) {
        this.setCustomValidity('Le budget ne peut pas être négatif');
    } else if (value > 100000000) { // 100M MAD
        this.setCustomValidity('Budget trop élevé, veuillez vérifier');
    } else {
        this.setCustomValidity('');
    }
});

// Formatage automatique du budget
document.getElementById('budget').addEventListener('blur', function() {
    if (this.value) {
        const value = parseFloat(this.value);
        this.value = value.toLocaleString('fr-FR');
    }
});
</script>
@endpush
@endsection
