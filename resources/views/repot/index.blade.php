@extends('layouts.app')

@section('title', 'Rapports d\'intervention - PlanifTech ORMVAT')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-file-alt text-warning me-2"></i>
                {{ Auth::user()->isAdmin() || Auth::user()->isChefEquipe() ? 'Gestion des rapports' : 'Mes rapports' }}
            </h1>
            <p class="text-muted mb-0">
                Rapports d'intervention et de maintenance des équipements ORMVAT
            </p>
        </div>
        <div class="d-flex gap-2">
            @if(Auth::user()->isAdmin() || Auth::user()->isChefEquipe())
                <a href="{{ route('reports.export', request()->query()) }}" class="btn btn-outline-success">
                    <i class="fas fa-download me-2"></i>
                    Exporter
                </a>
            @endif
            <a href="{{ route('reports.create') }}" class="btn btn-warning text-white">
                <i class="fas fa-plus me-2"></i>
                Nouveau rapport
            </a>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Recherche</label>
                    <input type="text" class="form-control" id="search" name="search"
                           value="{{ request('search') }}" placeholder="Titre, lieu, problème...">
                </div>

                <div class="col-md-2">
                    <label for="type" class="form-label">Type</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">Tous les types</option>
                        <option value="intervention" {{ request('type') === 'intervention' ? 'selected' : '' }}>Intervention</option>
                        <option value="maintenance" {{ request('type') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="inspection" {{ request('type') === 'inspection' ? 'selected' : '' }}>Inspection</option>
                        <option value="reparation" {{ request('type') === 'reparation' ? 'selected' : '' }}>Réparation</option>
                        <option value="installation" {{ request('type') === 'installation' ? 'selected' : '' }}>Installation</option>
                        <option value="autre" {{ request('type') === 'autre' ? 'selected' : '' }}>Autre</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="status" class="form-label">Statut</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Tous les statuts</option>
                        <option value="brouillon" {{ request('status') === 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                        <option value="soumis" {{ request('status') === 'soumis' ? 'selected' : '' }}>Soumis</option>
                        <option value="valide" {{ request('status') === 'valide' ? 'selected' : '' }}>Validé</option>
                        <option value="rejete" {{ request('status') === 'rejete' ? 'selected' : '' }}>Rejeté</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="date_from" class="form-label">Date début</label>
                    <input type="date" class="form-control" id="date_from" name="date_from"
                           value="{{ request('date_from') }}">
                </div>

                <div class="col-md-2">
                    <label for="date_to" class="form-label">Date fin</label>
                    <input type="date" class="form-control" id="date_to" name="date_to"
                           value="{{ request('date_to') }}">
                </div>

                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="fas fa-file-alt fa-2x text-warning mb-2"></i>
                    <h4 class="mb-0">{{ $reports->total() }}</h4>
                    <small class="text-muted">Total rapports</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-secondary">
                <div class="card-body text-center">
                    <i class="fas fa-edit fa-2x text-secondary mb-2"></i>
                    <h4 class="mb-0">{{ $reports->where('statut', 'brouillon')->count() }}</h4>
                    <small class="text-muted">Brouillons</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="fas fa-paper-plane fa-2x text-info mb-2"></i>
                    <h4 class="mb-0">{{ $reports->where('statut', 'soumis')->count() }}</h4>
                    <small class="text-muted">En attente</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <h4 class="mb-0">{{ $reports->where('statut', 'valide')->count() }}</h4>
                    <small class="text-muted">Validés</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des rapports -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Liste des rapports</h5>
            <div class="dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-sort me-1"></i>Trier par
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'date_intervention', 'direction' => 'desc']) }}">
                        <i class="fas fa-calendar me-2"></i>Date intervention (récent)
                    </a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'date_intervention', 'direction' => 'asc']) }}">
                        <i class="fas fa-calendar me-2"></i>Date intervention (ancien)
                    </a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'titre', 'direction' => 'asc']) }}">
                        <i class="fas fa-sort-alpha-down me-2"></i>Titre
                    </a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'statut', 'direction' => 'asc']) }}">
                        <i class="fas fa-flag me-2"></i>Statut
                    </a></li>
                </ul>
            </div>
        </div>
        <div class="card-body p-0">
            @forelse($reports as $report)
                <div class="report-item border-bottom p-3" data-report-id="{{ $report->id }}">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    @php
                                        $typeIcon = match($report->type) {
                                            'intervention' => 'fas fa-tools text-danger',
                                            'maintenance' => 'fas fa-wrench text-warning',
                                            'inspection' => 'fas fa-search text-info',
                                            'reparation' => 'fas fa-hammer text-primary',
                                            'installation' => 'fas fa-cog text-success',
                                            default => 'fas fa-file-alt text-secondary'
                                        };
                                    @endphp
                                    <i class="{{ $typeIcon }} fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">
                                        <a href="{{ route('reports.show', $report) }}" class="text-decoration-none">
                                            {{ $report->titre }}
                                        </a>
                                    </h6>
                                    <small class="text-muted">{{ Str::limit($report->description, 80) }}</small>
                                    <div class="mt-1">
                                        <span class="badge bg-{{ $report->type === 'intervention' ? 'danger' : ($report->type === 'maintenance' ? 'warning' : 'info') }}">
                                            {{ $report->type_label }}
                                        </span>
                                        @if($report->hasPhotos())
                                            <span class="badge bg-light text-dark ms-1">
                                                <i class="fas fa-camera me-1"></i>
                                                {{ $report->getPhotosCount() }} photo(s)
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="text-center">
                                <div class="fw-semibold">
                                    {{ $report->date_intervention->format('d/m/Y') }}
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    {{ Str::limit($report->lieu, 20) }}
                                </small>
                            </div>
                        </div>

                        <div class="col-md-2">
                            @if($report->user)
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center"
                                             style="width: 30px; height: 30px; font-size: 12px;">
                                            {{ $report->user->initials }}
                                        </div>
                                    </div>
                                    <div>
                                        <small class="fw-semibold">{{ $report->user->prenom }}</small>
                                        <br>
                                        <small class="text-muted">{{ $report->user->nom }}</small>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-2">
                            <div class="text-center">
                                <span class="badge bg-{{ $report->status_color }} fs-6">
                                    {{ $report->status_label }}
                                </span>
                                @if($report->cout_intervention)
                                    <div class="mt-1">
                                        <small class="text-muted">
                                            <i class="fas fa-dollar-sign me-1"></i>
                                            {{ number_format($report->cout_intervention, 0, ',', ' ') }} MAD
                                        </small>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-1">
                            <div class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="{{ route('reports.show', $report) }}">
                                            <i class="fas fa-eye me-2"></i>Voir détails
                                        </a></li>
                                        @can('update', $report)
                                            <li><a class="dropdown-item" href="{{ route('reports.edit', $report) }}">
                                                <i class="fas fa-edit me-2"></i>Modifier
                                            </a></li>
                                        @endcan
                                        <li><a class="dropdown-item" href="{{ route('reports.pdf', $report) }}" target="_blank">
                                            <i class="fas fa-file-pdf me-2"></i>Télécharger PDF
                                        </a></li>

                                        @if($report->statut === 'brouillon')
                                            <li><hr class="dropdown-divider"></li>
                                            <li><button class="dropdown-item report-action" data-action="submit" data-report-id="{{ $report->id }}">
                                                <i class="fas fa-paper-plane me-2"></i>Soumettre
                                            </button></li>
                                        @endif

                                        @can('validate', $report)
                                            @if($report->statut === 'soumis')
                                                <li><hr class="dropdown-divider"></li>
                                                <li><button class="dropdown-item text-success report-action" data-action="validate" data-report-id="{{ $report->id }}">
                                                    <i class="fas fa-check me-2"></i>Valider
                                                </button></li>
                                                <li><button class="dropdown-item text-danger report-action" data-action="reject" data-report-id="{{ $report->id }}">
                                                    <i class="fas fa-times me-2"></i>Rejeter
                                                </button></li>
                                            @endif
                                        @endcan

                                        @can('delete', $report)
                                            <li><hr class="dropdown-divider"></li>
                                            <li><button class="dropdown-item text-danger" onclick="confirmDelete({{ $report->id }}, '{{ $report->titre }}')">
                                                <i class="fas fa-trash me-2"></i>Supprimer
                                            </button></li>
                                        @endcan
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Détails supplémentaires -->
                    @if($report->probleme_identifie)
                        <div class="row mt-2">
                            <div class="col-12">
                                <div class="alert alert-light py-2">
                                    <small class="text-muted">
                                        <strong>Problème:</strong> {{ Str::limit($report->probleme_identifie, 150) }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($report->etat_equipement)
                        <div class="row mt-1">
                            <div class="col-12">
                                <small class="text-muted">
                                    <i class="fas fa-cogs me-1"></i>
                                    État équipement:
                                    <span class="badge bg-{{ match($report->etat_equipement) {
                                        'bon' => 'success',
                                        'moyen' => 'warning',
                                        'mauvais' => 'danger',
                                        'hors_service' => 'dark',
                                        default => 'secondary'
                                    } }}">
                                        {{ ucfirst(str_replace('_', ' ', $report->etat_equipement)) }}
                                    </span>
                                </small>
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucun rapport trouvé</h5>
                    <p class="text-muted">
                        @if(request()->hasAny(['search', 'type', 'status', 'date_from', 'date_to']))
                            Aucun rapport ne correspond à vos critères de recherche.
                        @else
                            Aucun rapport d'intervention disponible.
                        @endif
                    </p>
                    @if(!request()->hasAny(['search', 'type', 'status', 'date_from', 'date_to']))
                        <a href="{{ route('reports.create') }}" class="btn btn-warning text-white">
                            <i class="fas fa-plus me-2"></i>
                            Créer le premier rapport
                        </a>
                    @endif
                </div>
            @endforelse
        </div>

        @if($reports->hasPages())
            <div class="card-footer">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
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
                    <strong>Attention !</strong> Cette action supprimera également toutes les photos associées.
                </div>
                <p>Êtes-vous sûr de vouloir supprimer le rapport "<span id="reportTitle"></span>" ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de rejet -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rejeter le rapport</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="reason" class="form-label">Raison du rejet <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reason" name="reason" rows="3"
                                  placeholder="Expliquez pourquoi ce rapport est rejeté..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Rejeter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>
@endsection

@push('scripts')
<script>
// Actions sur les rapports
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('report-action')) {
        e.preventDefault();
        const action = e.target.dataset.action;
        const reportId = e.target.dataset.reportId;

        if (action === 'reject') {
            showRejectModal(reportId);
        } else {
            performReportAction(reportId, action);
        }
    }
});

function performReportAction(reportId, action) {
    let url, method, data = {};

    switch(action) {
        case 'submit':
            url = `/reports/${reportId}/submit`;
            method = 'PATCH';
            break;
        case 'validate':
            url = `/reports/${reportId}/validate`;
            method = 'PATCH';
            break;
        default:
            return;
    }

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Action effectuée avec succès', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Erreur lors de l\'action', 'danger');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showToast('Erreur de communication avec le serveur', 'danger');
    });
}

function showRejectModal(reportId) {
    document.getElementById('rejectForm').action = `/reports/${reportId}/reject`;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function confirmDelete(reportId, reportTitle) {
    document.getElementById('reportTitle').textContent = reportTitle;
    document.getElementById('deleteForm').action = `/reports/${reportId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    const toastId = 'toast-' + Date.now();
    const bgClass = type === 'success' ? 'bg-success' : type === 'danger' ? 'bg-danger' : 'bg-info';

    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', toastHtml);

    const toast = new bootstrap.Toast(document.getElementById(toastId), { autohide: true, delay: 4000 });
    toast.show();

    document.getElementById(toastId).addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}

// Auto-submit form on filter change
document.querySelectorAll('#type, #status').forEach(select => {
    select.addEventListener('change', function() {
        this.form.submit();
    });
});

// Animation des éléments au survol
document.querySelectorAll('.report-item').forEach(item => {
    item.addEventListener('mouseenter', function() {
        this.style.backgroundColor = '#f8f9fa';
        this.style.transition = 'background-color 0.2s ease';
    });

    item.addEventListener('mouseleave', function() {
        this.style.backgroundColor = '';
    });
});
</script>
@endpush

@push('styles')
<style>
.report-item {
    transition: all 0.2s ease;
}

.report-item:hover {
    background-color: #f8f9fa !important;
}

.badge {
    font-size: 0.75em;
    padding: 0.4em 0.6em;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}

.alert-light {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.dropdown-menu {
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.card {
    border-radius: 12px;
    border: none;
}
</style>
@endpush
@endsection
