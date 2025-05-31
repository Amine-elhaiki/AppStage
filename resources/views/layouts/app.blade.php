<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'PlanifTech ORMVAT')</title>

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
            --ormvat-light: #a8d5ba;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        /* Sidebar */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, var(--ormvat-primary) 0%, var(--ormvat-secondary) 100%);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            margin: 2px 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
            margin-right: 10px;
        }

        /* Header */
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-bottom: 3px solid var(--ormvat-accent);
        }

        .navbar-brand {
            font-weight: bold;
            color: var(--ormvat-primary) !important;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }

        .stats-card {
            background: linear-gradient(135deg, white 0%, #f8f9fa 100%);
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(45deg, var(--ormvat-primary), var(--ormvat-secondary));
            border: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(44, 85, 48, 0.3);
        }

        /* Progress bars */
        .progress {
            height: 8px;
            border-radius: 10px;
            background-color: #e9ecef;
        }

        .progress-bar {
            border-radius: 10px;
        }

        /* Badges */
        .badge {
            font-size: 0.75em;
            padding: 0.5em 0.8em;
            border-radius: 6px;
        }

        /* Utilities */
        .text-ormvat {
            color: var(--ormvat-primary);
        }

        .bg-ormvat {
            background-color: var(--ormvat-primary);
        }

        .border-ormvat {
            border-color: var(--ormvat-accent);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeInUp 0.5s ease-out;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }

            .main-content {
                margin-left: 0 !important;
            }
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--ormvat-accent);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--ormvat-secondary);
        }

        /* Status badges */
        .bg-status-a_faire { background-color: #6c757d !important; }
        .bg-status-en_cours { background-color: #0d6efd !important; }
        .bg-status-termine { background-color: #198754 !important; }
        .bg-status-reporte { background-color: #ffc107 !important; }
        .bg-status-annule { background-color: #dc3545 !important; }
    </style>
    @stack('styles')
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="sidebar d-none d-md-block" style="width: 250px;">
            <div class="p-3">
                <!-- Logo -->
                <div class="text-center mb-4">
                    <h4 class="text-white mb-1">
                        <i class="fas fa-water me-2"></i>
                        PlanifTech
                    </h4>
                    <small class="text-white-50">ORMVAT - Tadla</small>
                </div>

                <!-- User Info -->
                <div class="text-center mb-4 p-3 rounded" style="background-color: rgba(255,255,255,0.1);">
                    <div class="mb-2">
                        <div class="bg-white text-dark rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <strong>{{ auth()->user()->initials ?? 'U' }}</strong>
                        </div>
                    </div>
                    <div class="text-white">
                        <strong>{{ auth()->user()->prenom ?? 'Utilisateur' }} {{ auth()->user()->nom ?? '' }}</strong>
                        <br>
                        <small class="text-white-75">{{ ucfirst(auth()->user()->role ?? 'utilisateur') }}</small>
                    </div>
                </div>

                <!-- Navigation -->
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="fas fa-tachometer-alt"></i>
                            Tableau de bord
                        </a>
                    </li>

                    @if(auth()->user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('users*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                <i class="fas fa-users"></i>
                                Utilisateurs
                            </a>
                        </li>
                    @endif

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tasks*') ? 'active' : '' }}" href="{{ route('tasks.index') }}">
                            <i class="fas fa-tasks"></i>
                            Mes tâches
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('projects*') ? 'active' : '' }}" href="{{ route('projects.index') }}">
                            <i class="fas fa-project-diagram"></i>
                            Projets
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('events*') ? 'active' : '' }}" href="{{ route('events.index') }}">
                            <i class="fas fa-calendar"></i>
                            Événements
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('reports*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                            <i class="fas fa-file-alt"></i>
                            Rapports
                        </a>
                    </li>

                    <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('profile*') ? 'active' : '' }}" href="{{ route('profile.show') }}">
                            <i class="fas fa-user"></i>
                            Mon profil
                        </a>
                    </li>

                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="nav-link border-0 bg-transparent text-start w-100">
                                <i class="fas fa-sign-out-alt"></i>
                                Déconnexion
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="flex-grow-1">
            <!-- Header -->
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container-fluid">
                    <!-- Mobile toggle -->
                    <button class="navbar-toggler d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <span class="navbar-brand">
                        <i class="fas fa-water me-2 text-primary"></i>
                        PlanifTech ORMVAT
                    </span>

                    <!-- Right side -->
                    <div class="d-flex align-items-center">
                        <!-- Notifications -->
                        <div class="dropdown me-3">
                            <button class="btn btn-outline-secondary position-relative" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-bell"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    3
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header">Notifications</h6></li>
                                <li><a class="dropdown-item" href="#">Nouvelle tâche assignée</a></li>
                                <li><a class="dropdown-item" href="#">Projet mis à jour</a></li>
                                <li><a class="dropdown-item" href="#">Rapport validé</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-center" href="#">Voir toutes</a></li>
                            </ul>
                        </div>

                        <!-- User menu -->
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <strong>{{ auth()->user()->initials ?? 'U' }}</strong>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('profile.show') }}">
                                    <i class="fas fa-user me-2"></i>Mon profil
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main class="p-4">
                <!-- Alerts -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show animate-fade-in" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show animate-fade-in" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show animate-fade-in" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Mobile Sidebar -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSidebar">
        <div class="offcanvas-header bg-ormvat text-white">
            <h5 class="offcanvas-title">
                <i class="fas fa-water me-2"></i>
                PlanifTech
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <!-- Same navigation as sidebar but for mobile -->
            <div class="nav flex-column">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord
                </a>
                <a class="nav-link" href="{{ route('tasks.index') }}">
                    <i class="fas fa-tasks me-2"></i>Mes tâches
                </a>
                <a class="nav-link" href="{{ route('projects.index') }}">
                    <i class="fas fa-project-diagram me-2"></i>Projets
                </a>
                <a class="nav-link" href="{{ route('events.index') }}">
                    <i class="fas fa-calendar me-2"></i>Événements
                </a>
                <a class="nav-link" href="{{ route('reports.index') }}">
                    <i class="fas fa-file-alt me-2"></i>Rapports
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script>
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                if (alert.classList.contains('show')) {
                    new bootstrap.Alert(alert).close();
                }
            });
        }, 5000);

        // Add loading states to buttons
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('form').forEach(function(form) {
                form.addEventListener('submit', function() {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        const originalText = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Chargement...';
                        submitBtn.disabled = true;

                        // Restore button after 5 seconds as fallback
                        setTimeout(() => {
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        }, 5000);
                    }
                });
            });
        });

        // Global AJAX setup
        if (typeof $ !== 'undefined') {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        }
    </script>

    @stack('scripts')
</body>
</html>
