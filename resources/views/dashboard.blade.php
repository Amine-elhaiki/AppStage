@extends('layouts.app')

@section('title', 'Tableau de Bord')

@section('content')
<!-- Welcome Header -->
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-1">Bonjour {{ auth()->user()->prenom }} ! 👋</h2>
        <p class="text-muted">Voici un aperçu de vos activités aujourd'hui</p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <i class="fas fa-tasks fa-2x mb-3"></i>
                <div class="stat-number">24</div>
                <div>Tâches Actives</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card" style="background: linear-gradient(135deg, #e74c3c, #c0392b); color: white;">
            <div class="card-body text-center">
                <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                <div class="stat-number">3</div>
                <div>Tâches en Retard</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card" style="background: linear-gradient(135deg, #3498db, #2980b9); color: white;">
            <div class="card-body text-center">
                <i class="fas fa-calendar-day fa-2x mb-3"></i>
                <div class="stat-number">5</div>
                <div>Événements Aujourd'hui</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card" style="background: linear-gradient(135deg, #f39c12, #e67e22); color: white;">
            <div class="card-body text-center">
                <i class="fas fa-project-diagram fa-2x mb-3"></i>
                <div class="stat-number">8</div>
                <div>Projets Actifs</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Ma Journée -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-sun me-2"></i>Ma Journée</h5>
            </div>
            <div class="card-body">
                <!-- Tâches Prioritaires -->
                <h6 class="text-muted mb-3">Tâches Prioritaires</h6>
                <div class="list-group list-group-flush mb-4">
                    <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                        <div>
                            <h6 class="mb-1">Contrôle qualité eau irrigation Secteur A</h6>
                            <small class="text-muted">Échéance: Aujourd'hui</small>
                        </div>
                        <span class="badge bg-danger">Haute</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                        <div>
                            <h6 class="mb-1">Nettoyage filtres Station P8</h6>
                            <small class="text-muted">Assigné à: Mohammed Tazi</small>
                        </div>
                        <span class="badge bg-danger">Haute</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                        <div>
                            <h6 class="mb-1">Vérification vannes distribution Zone Est</h6>
                            <small class="text-muted">En cours - 30%</small>
                        </div>
                        <span class="badge bg-warning text-dark">Moyenne</span>
                    </div>
                </div>

                <!-- Événements du Jour -->
                <h6 class="text-muted mb-3">Événements du Jour</h6>
                <div class="timeline">
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <small>08:00</small>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Inspection Canal Principal B4</h6>
                            <small class="text-muted">Intervention technique - Canal Principal Secteur B4</small>
                        </div>
                    </div>
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <small>14:00</small>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Réunion Équipe Technique</h6>
                            <small class="text-muted">Réunion - Salle de Réunion</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Right -->
    <div class="col-lg-4">
        <!-- Weather Widget -->
        <div class="card mb-4" style="background: linear-gradient(135deg, #74b9ff, #0984e3); color: white;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">Khouribga</h5>
                        <p class="mb-0">Aujourd'hui</p>
                    </div>
                    <div class="text-end">
                        <h2 class="mb-0">24°C</h2>
                        <i class="fas fa-sun fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Projets en Cours -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-project-diagram me-2"></i>Projets en Cours</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted">Modernisation Tadla Nord</small>
                        <small class="text-muted">65%</small>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: 65%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted">Réhabilitation Canaux</small>
                        <small class="text-muted">40%</small>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: 40%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted">Maintenance Préventive</small>
                        <small class="text-muted">80%</small>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: 80%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mini Calendrier -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-calendar me-2"></i>Mars 2025</h6>
            </div>
            <div class="card-body">
                <div class="calendar-mini">
                    <div class="row text-center mb-2">
                        <div class="col"><small class="text-muted">L</small></div>
                        <div class="col"><small class="text-muted">M</small></div>
                        <div class="col"><small class="text-muted">M</small></div>
                        <div class="col"><small class="text-muted">J</small></div>
                        <div class="col"><small class="text-muted">V</small></div>
                        <div class="col"><small class="text-muted">S</small></div>
                        <div class="col"><small class="text-muted">D</small></div>
                    </div>
                    <div class="row text-center mb-1">
                        <div class="col"><div class="calendar-day">3</div></div>
                        <div class="col"><div class="calendar-day">4</div></div>
                        <div class="col"><div class="calendar-day has-event" style="background: #FFC107; border-radius: 50%; color: #000;">5</div></div>
                        <div class="col"><div class="calendar-day">6</div></div>
                        <div class="col"><div class="calendar-day">7</div></div>
                        <div class="col"><div class="calendar-day">8</div></div>
                        <div class="col"><div class="calendar-day">9</div></div>
                    </div>
                    <div class="row text-center mb-1">
                        <div class="col"><div class="calendar-day">10</div></div>
                        <div class="col"><div class="calendar-day">11</div></div>
                        <div class="col"><div class="calendar-day">12</div></div>
                        <div class="col"><div class="calendar-day">13</div></div>
                        <div class="col"><div class="calendar-day">14</div></div>
                        <div class="col"><div class="calendar-day today" style="background: var(--ormvat-primary); color: white; border-radius: 50%; font-weight: bold;">15</div></div>
                        <div class="col"><div class="calendar-day">16</div></div>
                    </div>
                    <div class="row text-center">
                        <div class="col"><div class="calendar-day">17</div></div>
                        <div class="col"><div class="calendar-day has-event" style="background: #FFC107; border-radius: 50%; color: #000;">18</div></div>
                        <div class="col"><div class="calendar-day">19</div></div>
                        <div class="col"><div class="calendar-day">20</div></div>
                        <div class="col"><div class="calendar-day">21</div></div>
                        <div class="col"><div class="calendar-day">22</div></div>
                        <div class="col"><div class="calendar-day">23</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div style="position: fixed; bottom: 30px; right: 30px; z-index: 1000;">
    <a href="{{ route('taches.create') }}" class="btn btn-success d-block mb-2" style="width: 60px; height: 60px; border-radius: 50%; font-size: 1.2rem;" data-bs-toggle="tooltip" title="Nouvelle Tâche">
        <i class="fas fa-plus"></i>
    </a>
    <a href="{{ route('evenements.create') }}" class="btn btn-primary d-block mb-2" style="width: 60px; height: 60px; border-radius: 50%; font-size: 1.2rem;" data-bs-toggle="tooltip" title="Nouvel Événement">
        <i class="fas fa-calendar-plus"></i>
    </a>
    <a href="{{ route('rapports.create') }}" class="btn btn-warning d-block" style="width: 60px; height: 60px; border-radius: 50%; font-size: 1.2rem;" data-bs-toggle="tooltip" title="Nouveau Rapport">
        <i class="fas fa-file-alt"></i>
    </a>
</div>
@endsection

@section('styles')
<style>
    .stat-card {
        background: linear-gradient(135deg, var(--ormvat-primary), var(--ormvat-secondary));
        color: white;
        border: none;
    }

    .stat-card .card-body {
        padding: 2rem;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: bold;
        line-height: 1;
    }

    .calendar-day {
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.875rem;
    }

    .calendar-day:hover {
        background: var(--ormvat-light);
        border-radius: 50%;
    }
</style>
@endsection

@section('scripts')
<script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>
@endsection
