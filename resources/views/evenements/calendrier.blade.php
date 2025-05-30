<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier des Événements - PlanifTech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <style>
        .fc-event {
            cursor: pointer;
        }
        .fc-event-intervention { background: #dc3545; }
        .fc-event-reunion { background: #0d6efd; }
        .fc-event-formation { background: #198754; }
        .fc-event-visite { background: #fd7e14; }
    </style>
</head>
<body>
    <!-- Navigation identique aux autres pages -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="/dashboard">
                <i class="fas fa-cogs me-2"></i>PlanifTech
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard">
                            <i class="fas fa-home me-1"></i>Tableau de bord
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/taches">
                            <i class="fas fa-tasks me-1"></i>Tâches
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/evenements">
                            <i class="fas fa-calendar me-1"></i>Événements
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/projets">
                            <i class="fas fa-project-diagram me-1"></i>Projets
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/rapports">
                            <i class="fas fa-file-alt me-1"></i>Rapports
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>Utilisateur
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/profile">Mon Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout">Déconnexion</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- En-tête -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-calendar-alt me-2"></i>Calendrier des Événements</h2>
            <div>
                <button class="btn btn-outline-primary me-2" onclick="window.location.href='/evenements'">
                    <i class="fas fa-list me-1"></i>Vue Liste
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nouvelEvenementModal">
                    <i class="fas fa-plus me-1"></i>Nouvel Événement
                </button>
            </div>
        </div>

        <!-- Légende des types d'événements -->
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="card-title">Légende des types d'événements</h6>
                <div class="row">
                    <div class="col-md-3">
                        <span class="badge bg-danger me-2">■</span>Intervention technique
                    </div>
                    <div class="col-md-3">
                        <span class="badge bg-primary me-2">■</span>Réunion
                    </div>
                    <div class="col-md-3">
                        <span class="badge bg-success me-2">■</span>Formation
                    </div>
                    <div class="col-md-3">
                        <span class="badge bg-warning me-2">■</span>Visite de terrain
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendrier -->
        <div class="card">
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    <!-- Modal Nouvel Événement -->
    <div class="modal fade" id="nouvelEvenementModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Créer un Nouvel Événement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="/evenements" method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Titre *</label>
                                    <input type="text" class="form-control" name="titre" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Type d'événement *</label>
                                    <select class="form-select" name="type" required>
                                        <option value="">Sélectionner le type</option>
                                        <option value="intervention">Intervention technique</option>
                                        <option value="reunion">Réunion</option>
                                        <option value="formation">Formation</option>
                                        <option value="visite">Visite de terrain</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Date et heure de début *</label>
                                    <input type="datetime-local" class="form-control" name="date_debut" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Date et heure de fin *</label>
                                    <input type="datetime-local" class="form-control" name="date_fin" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Lieu *</label>
                                    <input type="text" class="form-control" name="lieu" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Priorité</label>
                                    <select class="form-select" name="priorite">
                                        <option value="normale">Normale</option>
                                        <option value="haute">Haute</option>
                                        <option value="urgente">Urgente</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Participants</label>
                            <select class="form-select" name="participants[]" multiple>
                                <option value="1">Ahmed BENALI (Technicien)</option>
                                <option value="2">Fatima ALAOUI (Technicien)</option>
                                <option value="3">Mohammed TAZI (Administrateur)</option>
                                <option value="4">Laila MANSOURI (Technicien)</option>
                            </select>
                            <div class="form-text">Maintenez Ctrl pour sélectionner plusieurs participants</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Créer l'Événement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Détails Événement -->
    <div class="modal fade" id="detailsEvenementModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Détails de l'Événement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="evenement-details">
                        <!-- Les détails seront chargés dynamiquement -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-warning" id="btn-modifier-evenement">
                        <i class="fas fa-edit me-1"></i>Modifier
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales/fr.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'fr',
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                height: 600,
                events: [
                    {
                        title: 'Maintenance station P12',
                        start: '2024-12-15T09:00:00',
                        end: '2024-12-15T12:00:00',
                        className: 'fc-event-intervention',
                        extendedProps: {
                            type: 'intervention',
                            lieu: 'Station de pompage P12',
                            participants: ['Ahmed BENALI', 'Mohammed TAZI']
                        }
                    },
                    {
                        title: 'Réunion équipe technique',
                        start: '2024-12-16T14:00:00',
                        end: '2024-12-16T16:00:00',
                        className: 'fc-event-reunion',
                        extendedProps: {
                            type: 'reunion',
                            lieu: 'Salle de réunion ORMVAT',
                            participants: ['Toute l\'équipe']
                        }
                    },
                    {
                        title: 'Formation irrigation goutte-à-goutte',
                        start: '2024-12-18T10:00:00',
                        end: '2024-12-18T16:00:00',
                        className: 'fc-event-formation',
                        extendedProps: {
                            type: 'formation',
                            lieu: 'Centre de formation ORMVAT',
                            participants: ['Fatima ALAOUI', 'Laila MANSOURI']
                        }
                    }
                ],
                eventClick: function(info) {
                    // Afficher les détails de l'événement
                    const event = info.event;
                    const detailsHtml = `
                        <h6><i class="fas fa-calendar me-2"></i>${event.title}</h6>
                        <p><strong>Type:</strong> ${event.extendedProps.type}</p>
                        <p><strong>Date:</strong> ${event.start.toLocaleDateString('fr-FR')} ${event.start.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'})}</p>
                        <p><strong>Durée:</strong> ${event.end ? event.end.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'}) : 'Non spécifiée'}</p>
                        <p><strong>Lieu:</strong> ${event.extendedProps.lieu}</p>
                        <p><strong>Participants:</strong> ${event.extendedProps.participants.join(', ')}</p>
                    `;
                    document.getElementById('evenement-details').innerHTML = detailsHtml;
                    new bootstrap.Modal(document.getElementById('detailsEvenementModal')).show();
                }
            });
            calendar.render();
        });
    </script>
</body>
</html>

<!-- ================================================== -->
<!-- PAGE LISTE DES PROJETS -->
<!-- Emplacement: resources/views/projets/index.blade.php -->
<!-- ================================================== -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Projets - PlanifTech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .progress-container {
            position: relative;
        }
        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-weight: bold;
            font-size: 0.8rem;
        }
        .project-card {
            transition: transform 0.2s;
        }
        .project-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .status-badge {
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="/dashboard">
                <i class="fas fa-cogs me-2"></i>PlanifTech
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard">
                            <i class="fas fa-home me-1"></i>Tableau de bord
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/taches">
                            <i class="fas fa-tasks me-1"></i>Tâches
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/evenements">
                            <i class="fas fa-calendar me-1"></i>Événements
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/projets">
                            <i class="fas fa-project-diagram me-1"></i>Projets
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/rapports">
                            <i class="fas fa-file-alt me-1"></i>Rapports
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>Utilisateur
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/profile">Mon Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout">Déconnexion</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar Filtres -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-filter me-2"></i>Filtres</h5>
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="mb-3">
                                <label class="form-label">Statut</label>
                                <select class="form-select" name="statut">
                                    <option value="">Tous les statuts</option>
                                    <option value="planifie">Planifié</option>
                                    <option value="en_cours">En cours</option>
                                    <option value="termine">Terminé</option>
                                    <option value="suspendu">Suspendu</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Responsable</label>
                                <select class="form-select" name="responsable">
                                    <option value="">Tous les responsables</option>
                                    <option value="1">Ahmed BENALI</option>
                                    <option value="2">Mohammed TAZI</option>
                                    <option value="3">Fatima ALAOUI</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Zone géographique</label>
                                <select class="form-select" name="zone">
                                    <option value="">Toutes les zones</option>
                                    <option value="tadla-nord">Tadla Nord</option>
                                    <option value="tadla-sud">Tadla Sud</option>
                                    <option value="fkih-ben-salah">Fkih Ben Salah</option>
                                    <option value="beni-mellal">Béni Mellal</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Période</label>
                                <select class="form-select" name="periode">
                                    <option value="">Toutes les périodes</option>
                                    <option value="cette_semaine">Cette semaine</option>
                                    <option value="ce_mois">Ce mois</option>
                                    <option value="ce_trimestre">Ce trimestre</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i>Filtrer
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Statistiques rapides -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6><i class="fas fa-chart-pie me-2"></i>Statistiques</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <h4 class="text-primary">12</h4>
                                <small>Total projets</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-success">8</h4>
                                <small>En cours</small>
                            </div>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col-6">
                                <h4 class="text-warning">3</h4>
                                <small>En retard</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-info">67%</h4>
                                <small>Avancement moyen</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenu principal -->
            <div class="col-md-9">
                <!-- En-tête -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-project-diagram me-2"></i>Gestion des Projets</h2>
                    <div>
                        <button class="btn btn-outline-primary me-2" onclick="toggleView()">
                            <i class="fas fa-th-large me-1" id="view-icon"></i>
                            <span id="view-text">Vue Grille</span>
                        </button>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nouveauProjetModal">
                            <i class="fas fa-plus me-1"></i>Nouveau Projet
                        </button>
                    </div>
                </div>

                <!-- Barre de recherche -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" class="form-control" placeholder="Rechercher un projet...">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select">
                                    <option>Trier par nom</option>
                                    <option>Trier par date</option>
                                    <option>Trier par avancement</option>
                                    <option>Trier par priorité</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vue Grille des Projets (par défaut) -->
                <div id="grid-view">
                    <div class="row">
                        <!-- Projet 1 -->
                        <div class="col-lg-6 col-xl-4 mb-4">
                            <div class="card project-card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Modernisation Réseau Irrigation B4</h6>
                                    <span class="badge bg-success status-badge">En cours</span>
                                </div>
                                <div class="card-body">
                                    <p class="card-text text-muted small">
                                        Modernisation complète du réseau d'irrigation du secteur B4 avec installation de nouveaux équipements automatisés.
                                    </p>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="text-muted">Avancement</small>
                                            <small class="text-muted">75%</small>
                                        </div>
                                        <div class="progress progress-container" style="height: 20px;">
                                            <div class="progress-bar bg-success" style="width: 75%"></div>
                                            <span class="progress-text">75%</span>
                                        </div>
                                    </div>
                                    <div class="row text-center small">
                                        <div class="col-4">
                                            <i class="fas fa-tasks text-primary"></i>
                                            <div>12 tâches</div>
                                        </div>
                                        <div class="col-4">
                                            <i class="fas fa-check-circle text-success"></i>
                                            <div>9 terminées</div>
                                        </div>
                                        <div class="col-4">
                                            <i class="fas fa-clock text-warning"></i>
                                            <div>3 restantes</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i>Ahmed BENALI
                                            </small>
                                        </div>
                                        <div>
                                            <button class="btn btn-sm btn-outline-primary me-1" onclick="voirProjet(1)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" onclick="modifierProjet(1)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Projet 2 -->
                        <div class="col-lg-6 col-xl-4 mb-4">
                            <div class="card project-card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Maintenance Préventive Stations</h6>
                                    <span class="badge bg-info status-badge">Planifié</span>
                                </div>
                                <div class="card-body">
                                    <p class="card-text text-muted small">
                                        Programme de maintenance préventive annuelle de toutes les stations de pompage de la région.
                                    </p>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="text-muted">Avancement</small>
                                            <small class="text-muted">25%</small>
                                        </div>
                                        <div class="progress progress-container" style="height: 20px;">
                                            <div class="progress-bar bg-info" style="width: 25%"></div>
                                            <span class="progress-text">25%</span>
                                        </div>
                                    </div>
                                    <div class="row text-center small">
                                        <div class="col-4">
                                            <i class="fas fa-tasks text-primary"></i>
                                            <div>20 tâches</div>
                                        </div>
                                        <div class="col-4">
                                            <i class="fas fa-check-circle text-success"></i>
                                            <div>5 terminées</div>
                                        </div>
                                        <div class="col-4">
                                            <i class="fas fa-clock text-warning"></i>
                                            <div>15 restantes</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i>Mohammed TAZI
                                            </small>
                                        </div>
                                        <div>
                                            <button class="btn btn-sm btn-outline-primary me-1" onclick="voirProjet(2)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" onclick="modifierProjet(2)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Projet 3 -->
                        <div class="col-lg-6 col-xl-4 mb-4">
                            <div class="card project-card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Formation Techniques Irrigation</h6>
                                    <span class="badge bg-warning status-badge">En retard</span>
                                </div>
                                <div class="card-body">
                                    <p class="card-text text-muted small">
                                        Programme de formation des agriculteurs aux nouvelles techniques d'irrigation économe en eau.
                                    </p>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="text-muted">Avancement</small>
                                            <small class="text-muted">45%</small>
                                        </div>
                                        <div class="progress progress-container" style="height: 20px;">
                                            <div class="progress-bar bg-warning" style="width: 45%"></div>
                                            <span class="progress-text">45%</span>
                                        </div>
                                    </div>
                                    <div class="row text-center small">
                                        <div class="col-4">
                                            <i class="fas fa-tasks text-primary"></i>
                                            <div>8 tâches</div>
                                        </div>
                                        <div class="col-4">
                                            <i class="fas fa-check-circle text-success"></i>
                                            <div>3 terminées</div>
                                        </div>
                                        <div class="col-4">
                                            <i class="fas fa-exclamation-triangle text-danger"></i>
                                            <div>2 en retard</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i>Fatima ALAOUI
                                            </small>
                                        </div>
                                        <div>
                                            <button class="btn btn-sm btn-outline-primary me-1" onclick="voirProjet(3)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" onclick="modifierProjet(3)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vue Liste (cachée par défaut) -->
                <div id="list-view" style="display: none;">
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Projet</th>
                                        <th>Responsable</th>
                                        <th>Zone</th>
                                        <th>Avancement</th>
                                        <th>Statut</th>
                                        <th>Échéance</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div>
                                                <h6 class="mb-1">Modernisation Réseau Irrigation B4</h6>
                                                <small class="text-muted">12 tâches - 9 terminées</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-circle text-muted me-2"></i>
                                                Ahmed BENALI
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">Tadla Nord</span>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 8px; width: 100px;">
                                                <div class="progress-bar bg-success" style="width: 75%"></div>
                                            </div>
                                            <small class="text-muted">75%</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">En cours</span>
                                        </td>
                                        <td>
                                            <small>31/12/2024</small>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-1" onclick="voirProjet(1)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" onclick="modifierProjet(1)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div>
                                                <h6 class="mb-1">Maintenance Préventive Stations</h6>
                                                <small class="text-muted">20 tâches - 5 terminées</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-circle text-muted me-2"></i>
                                                Mohammed TAZI
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">Toutes zones</span>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 8px; width: 100px;">
                                                <div class="progress-bar bg-info" style="width: 25%"></div>
                                            </div>
                                            <small class="text-muted">25%</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">Planifié</span>
                                        </td>
                                        <td>
                                            <small>15/03/2025</small>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-1" onclick="voirProjet(2)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" onclick="modifierProjet(2)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div>
                                                <h6 class="mb-1">Formation Techniques Irrigation</h6>
                                                <small class="text-muted">8 tâches - 3 terminées</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-circle text-muted me-2"></i>
                                                Fatima ALAOUI
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">Fkih Ben Salah</span>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 8px; width: 100px;">
                                                <div class="progress-bar bg-warning" style="width: 45%"></div>
                                            </div>
                                            <small class="text-muted">45%</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">En retard</span>
                                        </td>
                                        <td>
                                            <small class="text-danger">01/12/2024</small>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-1" onclick="voirProjet(3)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" onclick="modifierProjet(3)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">Précédent</a>
                        </li>
                        <li class="page-item active">
                            <a class="page-link" href="#">1</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#">2</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#">3</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#">Suivant</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Modal Nouveau Projet -->
    <div class="modal fade" id="nouveauProjetModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Créer un Nouveau Projet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="/projets" method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Nom du projet *</label>
                                    <input type="text" class="form-control" name="nom" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Statut initial</label>
                                    <select class="form-select" name="statut">
                                        <option value="planifie">Planifié</option>
                                        <option value="en_cours">En cours</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Description détaillée du projet..."></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Date de début *</label>
                                    <input type="date" class="form-control" name="date_debut" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Date de fin prévue *</label>
                                    <input type="date" class="form-control" name="date_fin" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Responsable du projet *</label>
                                    <select class="form-select" name="id_responsable" required>
                                        <option value="">Sélectionner le responsable</option>
                                        <option value="1">Ahmed BENALI (Technicien)</option>
                                        <option value="2">Mohammed TAZI (Administrateur)</option>
                                        <option value="3">Fatima ALAOUI (Technicien)</option>
                                        <option value="4">Laila MANSOURI (Technicien)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Zone géographique</label>
                                    <select class="form-select" name="zone_geographique">
                                        <option value="">Sélectionner la zone</option>
                                        <option value="tadla-nord">Tadla Nord</option>
                                        <option value="tadla-sud">Tadla Sud</option>
                                        <option value="fkih-ben-salah">Fkih Ben Salah</option>
                                        <option value="beni-mellal">Béni Mellal</option>
                                        <option value="toutes-zones">Toutes les zones</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Budget estimé (DH)</label>
                            <input type="number" class="form-control" name="budget" placeholder="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Priorité</label>
                            <select class="form-select" name="priorite">
                                <option value="normale">Normale</option>
                                <option value="haute">Haute</option>
                                <option value="critique">Critique</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Créer le Projet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Détails Projet -->
    <div class="modal fade" id="detailsProjetModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Détails du Projet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="projet-details">
                        <!-- Contenu dynamique du projet -->
                        <div class="row">
                            <div class="col-md-8">
                                <h6>Modernisation Réseau Irrigation B4</h6>
                                <p class="text-muted">Modernisation complète du réseau d'irrigation du secteur B4 avec installation de nouveaux équipements automatisés pour améliorer l'efficacité de l'irrigation.</p>

                                <h6 class="mt-4">Tâches associées</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Tâche</th>
                                                <th>Assigné à</th>
                                                <th>Statut</th>
                                                <th>Échéance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Inspection préliminaire du secteur</td>
                                                <td>Ahmed BENALI</td>
                                                <td><span class="badge bg-success">Terminé</span></td>
                                                <td>15/11/2024</td>
                                            </tr>
                                            <tr>
                                                <td>Commande équipements automatisés</td>
                                                <td>Mohammed TAZI</td>
                                                <td><span class="badge bg-success">Terminé</span></td>
                                                <td>20/11/2024</td>
                                            </tr>
                                            <tr>
                                                <td>Installation nouveaux capteurs</td>
                                                <td>Ahmed BENALI</td>
                                                <td><span class="badge bg-warning">En cours</span></td>
                                                <td>25/12/2024</td>
                                            </tr>
                                            <tr>
                                                <td>Tests et calibrage système</td>
                                                <td>Fatima ALAOUI</td>
                                                <td><span class="badge bg-secondary">À faire</span></td>
                                                <td>30/12/2024</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Informations du projet</h6>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Responsable:</strong><br>Ahmed BENALI</p>
                                        <p><strong>Zone:</strong><br>Tadla Nord</p>
                                        <p><strong>Période:</strong><br>01/11/2024 - 31/12/2024</p>
                                        <p><strong>Budget:</strong><br>450 000 DH</p>
                                        <p><strong>Statut:</strong><br><span class="badge bg-success">En cours</span></p>

                                        <div class="mt-3">
                                            <h6>Avancement global</h6>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-success" style="width: 75%">75%</div>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <h6>Statistiques</h6>
                                            <div class="row text-center">
                                                <div class="col-4">
                                                    <h5 class="text-primary">12</h5>
                                                    <small>Total tâches</small>
                                                </div>
                                                <div class="col-4">
                                                    <h5 class="text-success">9</h5>
                                                    <small>Terminées</small>
                                                </div>
                                                <div class="col-4">
                                                    <h5 class="text-warning">3</h5>
                                                    <small>Restantes</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-warning" onclick="modifierProjet(1)">
                        <i class="fas fa-edit me-1"></i>Modifier
                    </button>
                    <button type="button" class="btn btn-primary">
                        <i class="fas fa-download me-1"></i>Exporter PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentView = 'grid';

        function toggleView() {
            const gridView = document.getElementById('grid-view');
            const listView = document.getElementById('list-view');
            const viewIcon = document.getElementById('view-icon');
            const viewText = document.getElementById('view-text');

            if (currentView === 'grid') {
                gridView.style.display = 'none';
                listView.style.display = 'block';
                viewIcon.className = 'fas fa-th me-1';
                viewText.textContent = 'Vue Grille';
                currentView = 'list';
            } else {
                gridView.style.display = 'block';
                listView.style.display = 'none';
                viewIcon.className = 'fas fa-list me-1';
                viewText.textContent = 'Vue Liste';
                currentView = 'grid';
            }
        }

        function voirProjet(id) {
            // Charger les détails du projet et afficher la modal
            new bootstrap.Modal(document.getElementById('detailsProjetModal')).show();
        }

        function modifierProjet(id) {
            // Rediriger vers la page de modification ou ouvrir une modal d'édition
            alert('Redirection vers la modification du projet ' + id);
        }

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            // Ajout d'événements pour les filtres
            const filterInputs = document.querySelectorAll('select[name], input[name]');
            filterInputs.forEach(input => {
                input.addEventListener('change', function() {
                    // Logique de filtrage côté client ou appel AJAX
                    console.log('Filtre changé:', this.name, this.value);
                });
            });
        });
    </script>
</body>
</html>
