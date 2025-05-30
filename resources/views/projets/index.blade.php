@extends('layouts.app')

@section('title', 'Gestion des projets - PlanifTech ORMVAT')

@section('content')
<div class="container-fluid py-4">
    <!-- En-tête -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-dark">
                        <i class="fas fa-project-diagram me-2 text-primary"></i>
                        Gestion des projets
                    </h1>
                    <p class="text-muted mb-0">
                        Suivez et organisez les projets hydrauliques et agricoles de l'ORMVAT
                    </p>
                </div>
                <div>
                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('projects.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>
                            Nouveau projet
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-project-diagram fa-2x opacity-75"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0 fw-bold">{{ $stats['total_projects'] ?? 0 }}</h4>
                            <p class="mb-0 opacity-75">Projets actifs</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0 fw-bold">{{ $stats['completed_projects'] ?? 0 }}</h4>
                            <p class="mb-0 opacity-75">Projets terminés</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0 fw-bold">{{ $stats['delayed_projects'] ?? 0 }}</h4>
                            <p class="mb-0 opacity-75">En retard</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-chart-line fa-2x opacity-75"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0 fw-bold">{{ $stats['avg_completion'] ?? 0 }}%</h4>
                            <p class="mb-0 opacity-75">Avancement moyen</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('projects.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Recherche</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="search" name="search"
                               value="{{ request('search') }}" placeholder="Nom, description, zone...">
                    </div>
                </div>

                <div class="col-md-2">
                    <label for="statut" class="form-label">Statut</label>
                    <select class="form-select" id="statut" name="statut">
                        <option value="">Tous les statuts</option>
                        <option value="planifie" {{ request('statut') === 'planifie' ? 'selected' : '' }}>Planifié</option>
                        <option value="en_cours" {{ request('statut') === 'en_cours' ? 'selected' : '' }}>En cours</option>
                        <option value="termine" {{ request('statut') === 'termine' ? 'selected' : '' }}>Terminé</option>
                        <option value="suspendu" {{ request('statut') === 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="zone" class="form-label">Zone géographique</label>
                    <select class="form-select" id="zone" name="zone">
                        <option value="">Toutes les zones</option>
                        @foreach($zones ?? [] as $zone)
                            <option value="{{ $zone }}" {{ request('zone') === $zone ? 'selected' : '' }}>
                                {{ $zone }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if(Auth::user()->role === 'admin')
                <div class="col-md-2">
                    <label for="responsable" class="form-label">Responsable</label>
                    <select class="form-select" id="responsable" name="responsable">
                        <option value="">Tous les responsables</option>
                        @foreach($responsables ?? [] as $responsable)
                            <option value="{{ $responsable->id }}" {{ request('responsable') == $responsable->id ? 'selected' : '' }}>
                                {{ $responsable->prenom }} {{ $responsable->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Filtrer
                    </button>
                    <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>Réinitialiser
                    </a>
                    <div class="float-end">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary" onclick="exportProjects('excel')">
                                <i class="fas fa-file-excel me-1"></i>Excel
                            </button>
                            <button type="button" class="btn btn-outline-primary" onclick="exportProjects('pdf')">
                                <i class="fas fa-file-pdf me-1"></i>PDF
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des projets -->
    <div class="row">
        @forelse($projects ?? [] as $project)
            <div class="col-lg-6 col-xl-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <!-- En-tête de la carte -->
                    <div class="card-header bg-white border-bottom-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-{{ $project->statut === 'termine' ? 'success' : ($project->statut === 'en_cours' ? 'primary' : ($project->statut === 'suspendu' ? 'danger' : 'secondary')) }}">
                                {{ ucfirst(str_replace('_', ' ', $project->statut)) }}
                            </span>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('projects.show', $project->id) }}">
                                        <i class="fas fa-eye me-2"></i>Voir détails
                                    </a></li>
                                    @if(Auth::user()->role === 'admin')
                                        <li><a class="dropdown-item" href="{{ route('projects.edit', $project->id) }}">
                                            <i class="fas fa-edit me-2"></i>Modifier
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteProject({{ $project->id }})">
                                            <i class="fas fa-trash me-2"></i>Supprimer
                                        </a></li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Contenu de la carte -->
                    <div class="card-body">
                        <h5 class="card-title mb-2">{{ $project->nom }}</h5>
                        <p class="card-text text-muted mb-3">{{ Str::limit($project->description, 100) }}</p>

                        <!-- Informations clés -->
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                <small class="text-muted">{{ $project->zone_geographique }}</small>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-user text-muted me-2"></i>
                                <small class="text-muted">{{ $project->responsable->prenom ?? '' }} {{ $project->responsable->nom ?? '' }}</small>
                            </div>
                            @if($project->date_fin)
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-calendar text-muted me-2"></i>
                                    <small class="text-muted">
                                        Échéance: {{ Carbon\Carbon::parse($project->date_fin)->format('d/m/Y') }}
                                        @if(Carbon\Carbon::parse($project->date_fin)->isPast() && $project->statut !== 'termine')
                                            <span class="text-danger ms-1">(En retard)</span>
                                        @endif
                                    </small>
                                </div>
                            @endif
                        </div>

                        <!-- Barre de progression -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="text-muted">Avancement</small>
                                <small class="fw-bold text-{{ $project->pourcentage_avancement >= 75 ? 'success' : ($project->pourcentage_avancement >= 50 ? 'warning' : 'danger') }}">
                                    {{ $project->pourcentage_avancement ?? 0 }}%
                                </small>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-{{ $project->pourcentage_avancement >= 75 ? 'success' : ($project->pourcentage_avancement >= 50 ? 'warning' : 'danger') }}"
                                     role="progressbar"
                                     style="width: {{ $project->pourcentage_avancement ?? 0 }}%"></div>
                            </div>
                        </div>

                        <!-- Statistiques du projet -->
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="small text-muted">Tâches</div>
                                <div class="fw-bold text-primary">{{ $project->taches_count ?? 0 }}</div>
                            </div>
                            <div class="col-4">
                                <div class="small text-muted">Événements</div>
                                <div class="fw-bold text-success">{{ $project->evenements_count ?? 0 }}</div>
                            </div>
                            <div class="col-4">
                                <div class="small text-muted">Rapports</div>
                                <div class="fw-bold text-info">{{ $project->rapports_count ?? 0 }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Pied de carte -->
                    <div class="card-footer bg-white border-top-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                Créé {{ $project->created_at->diffForHumans() }}
                            </small>
                            <a href="{{ route('projects.show', $project->id) }}" class="btn btn-primary btn-sm">
                                Voir détails <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-project-diagram fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">Aucun projet trouvé</h4>
                    <p class="text-muted">
                        @if(request()->hasAny(['search', 'statut', 'zone', 'responsable']))
                            Aucun projet ne correspond à vos critères de recherche.
                            <br><a href="{{ route('projects.index') }}">Voir tous les projets</a>
                        @else
                            Commencez par créer votre premier projet ORMVAT.
                        @endif
                    </p>
                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('projects.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Créer le premier projet
                        </a>
                    @endif
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if(isset($projects) && $projects->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $projects->appends(request()->query())->links() }}
        </div>
    @endif
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Attention !</strong> Cette action supprimera définitivement le projet ainsi que :
                </div>
                <ul class="mb-0">
                    <li>Toutes les tâches associées</li>
                    <li>Tous les événements liés</li>
                    <li>Tous les rapports du projet</li>
                    <li>L'historique complet</li>
                </ul>
                <p class="text-danger mt-3 mb-0">Cette action est <strong>irréversible</strong>.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Supprimer définitivement
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Fonction de suppression
function deleteProject(projectId) {
    const deleteForm = document.getElementById('deleteForm');
    deleteForm.action = `/projects/${projectId}`;

    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Export des projets
function exportProjects(format) {
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('export', format);

    // Créer un lien de téléchargement temporaire
    const link = document.createElement('a');
    link.href = currentUrl.toString();
    link.download = `projets_ormvat_${new Date().toISOString().split('T')[0]}.${format}`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Tri des projets
function sortProjects(field, direction = 'asc') {
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('sort', field);
    currentUrl.searchParams.set('direction', direction);
    window.location.href = currentUrl.toString();
}

// Recherche en temps réel
let searchTimeout;
document.getElementById('search').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const searchTerm = this.value;

    if (searchTerm.length >= 3 || searchTerm.length === 0) {
        searchTimeout = setTimeout(() => {
            // Soumettre automatiquement le formulaire après 500ms d'inactivité
            this.closest('form').submit();
        }, 500);
    }
});

// Animation des cartes au survol
document.querySelectorAll('.card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-5px)';
        this.style.transition = 'transform 0.2s ease-in-out';
    });

    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
    });
});

// Mise à jour en temps réel des statistiques (optionnel)
function refreshStats() {
    fetch('/api/projects/stats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mettre à jour les statistiques sans recharger la page
                console.log('Statistiques mises à jour');
            }
        })
        .catch(error => console.log('Erreur refresh stats:', error));
}

// Actualiser les stats toutes les 5 minutes
setInterval(refreshStats, 300000);
</script>
@endpush
@endsection
