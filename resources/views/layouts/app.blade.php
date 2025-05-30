<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'PlanifTech') - ORMVAT</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --ormvat-primary: #2c5530;
            --ormvat-secondary: #4a7c59;
            --ormvat-accent: #7fb069;
            --ormvat-light: #f8f9fa;
            --ormvat-dark: #1a3a1d;
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--ormvat-primary) !important;
        }

        .btn-primary {
            background-color: var(--ormvat-primary);
            border-color: var(--ormvat-primary);
        }

        .btn-primary:hover {
            background-color: var(--ormvat-secondary);
            border-color: var(--ormvat-secondary);
        }

        .nav-link.active {
            background-color: var(--ormvat-accent) !important;
            color: white !important;
        }

        .sidebar {
            min-height: calc(100vh - 56px);
            background-color: var(--ormvat-light);
            border-right: 1px solid #dee2e6;
        }

        .main-content {
            min-height: calc(100vh - 56px);
        }

        .card-header {
            background-color: var(--ormvat-primary);
            color: white;
        }

        .badge.bg-priority-haute { background-color: #dc3545 !important; }
        .badge.bg-priority-moyenne { background-color: #ffc107 !important; color: #000; }
        .badge.bg-priority-basse { background-color: #28a745 !important; }

        .badge.bg-status-a-faire { background-color: #6c757d !important; }
        .badge.bg-status-en-cours { background-color: #0d6efd !important; }
        .badge.bg-status-termine { background-color: #198754 !important; }

        .progress-sm { height: 8px; }

        .stats-card {
            transition: transform 0.2s;
        }

        .stats-card:hover {
            transform: translateY(-2px);
        }

        .quick-actions .btn {
            margin: 0.25rem;
        }

        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            transform: translate(50%, -50%);
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
                <i class="fas fa-water text-primary me-2"></i>
                <strong>PlanifTech</strong>
                <small class="text-muted ms-2">ORMVAT</small>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="fas fa-tachometer-alt me-1"></i> Tableau de bord
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tasks.*') ? 'active' : '' }}" href="{{ route('tasks.index') }}">
                            <i class="fas fa-tasks me-1"></i> Tâches
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('projects.*') ? 'active' : '' }}" href="{{ route('projects.index') }}">
                            <i class="fas fa-project-diagram me-1"></i> Projets
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('events.*') ? 'active' : '' }}" href="{{ route('events.index') }}">
                            <i class="fas fa-calendar me-1"></i> Événements
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                            <i class="fas fa-file-alt me-1"></i> Rapports
                        </a>
                    </li>
                    @can('admin-access')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                            <i class="fas fa-users me-1"></i> Utilisateurs
                        </a>
                    </li>
                    @endcan
                </ul>

                <ul class="navbar-nav">
                    <!-- Notifications -->
                    <li class="nav-item dropdown position-relative">
                        <a class="nav-link" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            <span class="badge bg-danger notification-badge" style="display: none;" id="notificationCount">0</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" style="width: 300px;">
                            <li><h6 class="dropdown-header">Notifications</h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li id="notificationsList">
                                <div class="text-center p-3 text-muted">
                                    <small>Aucune notification</small>
                                </div>
                            </li>
                        </ul>
                    </li>

                    <!-- Profil utilisateur -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                {{ auth()->user()->initials }}
                            </div>
                            <span class="d-none d-md-inline">{{ auth()->user()->full_name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">{{ auth()->user()->full_name }}</h6></li>
                            <li><small class="text-muted px-3">{{ ucfirst(auth()->user()->role) }}</small></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('profile.show') }}">
                                <i class="fas fa-user me-2"></i> Mon profil
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenu principal -->
    <div class="container-fluid p-0">
        <div class="row g-0">
            @hasSection('sidebar')
            <div class="col-md-3 col-lg-2 sidebar p-3">
                @yield('sidebar')
            </div>
            <div class="col-md-9 col-lg-10 main-content">
            @else
            <div class="col-12 main-content">
            @endif
                <!-- Messages Flash -->
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show m-3" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <!-- Contenu de la page -->
                <div class="p-3">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js pour les graphiques -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Scripts personnalisés -->
    <script>
        // Configuration CSRF pour AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Fonction pour charger les notifications
        function loadNotifications() {
            fetch('/api/notifications/unread-count')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('notificationCount');
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'inline';
                    } else {
                        badge.style.display = 'none';
                    }
                })
                .catch(error => console.log('Erreur chargement notifications:', error));
        }

        // Charger les notifications au démarrage
        document.addEventListener('DOMContentLoaded', function() {
            loadNotifications();
            // Recharger toutes les 30 secondes
            setInterval(loadNotifications, 30000);
        });

        // Fonction utilitaire pour formater les dates
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('fr-FR', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Fonction pour afficher les toasts
        function showToast(message, type = 'info') {
            const toastHtml = `
                <div class="toast align-items-center text-white bg-${type} border-0" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">${message}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;

            let toastContainer = document.getElementById('toastContainer');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toastContainer';
                toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
                toastContainer.style.zIndex = '9999';
                document.body.appendChild(toastContainer);
            }

            toastContainer.insertAdjacentHTML('beforeend', toastHtml);
            const toastElement = toastContainer.lastElementChild;
            const toast = new bootstrap.Toast(toastElement);
            toast.show();

            // Supprimer l'élément après fermeture
            toastElement.addEventListener('hidden.bs.toast', () => {
                toastElement.remove();
            });
        }
    </script>

    @stack('scripts')
</body>
</html>
