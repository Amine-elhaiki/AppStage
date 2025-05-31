<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PlanifTech ORMVAT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="fas fa-tasks"></i> PlanifTech ORMVAT
            </a>

            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i> {{ $user->prenom }} {{ $user->nom }}
                        <span class="badge bg-light text-dark ms-1">{{ $user->role }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('profile.show') }}">
                            <i class="fas fa-user-edit"></i> Mon profil
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenu principal -->
    <div class="container mt-4">
        <!-- Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 bg-gradient-primary text-white">
                    <div class="card-body">
                        <h1 class="h3 mb-2">
                            <i class="fas fa-home"></i> {{ $message ?? 'Tableau de bord' }}
                        </h1>
                        <p class="mb-0">
                            Bienvenue dans votre espace de travail PlanifTech - {{ now()->format('d/m/Y') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-users fa-2x text-primary mb-2"></i>
                        <h4>{{ $stats['total_users'] ?? 0 }}</h4>
                        <p class="text-muted">Utilisateurs total</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-user-tag fa-2x text-success mb-2"></i>
                        <h4>{{ ucfirst($stats['your_role'] ?? 'N/A') }}</h4>
                        <p class="text-muted">Votre rôle</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-check-circle fa-2x text-info mb-2"></i>
                        <h4>{{ ucfirst($stats['your_status'] ?? 'N/A') }}</h4>
                        <p class="text-muted">Statut du compte</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-bolt"></i> Actions rapides</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <a href="{{ route('profile.show') }}" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-user-edit"></i><br>
                                    <small>Mon profil</small>
                                </a>
                            </div>

                            @if($user->role === 'admin')
                            <div class="col-md-3 mb-2">
                                <a href="{{ route('admin.register') }}" class="btn btn-outline-success w-100">
                                    <i class="fas fa-user-plus"></i><br>
                                    <small>Nouvel utilisateur</small>
                                </a>
                            </div>
                            @endif

                            <div class="col-md-3 mb-2">
                                <a href="/test-auth" class="btn btn-outline-info w-100" target="_blank">
                                    <i class="fas fa-cog"></i><br>
                                    <small>Test authentification</small>
                                </a>
                            </div>

                            <div class="col-md-3 mb-2">
                                <a href="/health" class="btn btn-outline-secondary w-100" target="_blank">
                                    <i class="fas fa-heartbeat"></i><br>
                                    <small>État du système</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations de debug -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-header">
                        <h6><i class="fas fa-info-circle"></i> Informations de debug</h6>
                    </div>
                    <div class="card-body">
                        <small>
                            <strong>ID utilisateur :</strong> {{ $user->id }}<br>
                            <strong>Email :</strong> {{ $user->email }}<br>
                            <strong>Dernière connexion :</strong> {{ $user->dernier_connexion ? $user->dernier_connexion->format('d/m/Y H:i') : 'Jamais' }}<br>
                            <strong>Environnement :</strong> {{ app()->environment() }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</body>
</html>
