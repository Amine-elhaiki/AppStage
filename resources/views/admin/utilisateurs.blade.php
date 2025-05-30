<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - PlanifTech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                        <a class="nav-link" href="/projets">
                            <i class="fas fa-project-diagram me-1"></i>Projets
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/rapports">
                            <i class="fas fa-file-alt me-1"></i>Rapports
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog me-1"></i>Administration
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item active" href="/admin/utilisateurs">Utilisateurs</a></li>
                            <li><a class="dropdown-item" href="/admin/logs">Journaux</a></li>
                            <li><a class="dropdown-item" href="/admin/config">Configuration</a></li>
                        </ul>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>Administrateur
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
            <h2><i class="fas fa-users me-2"></i>Gestion des Utilisateurs</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nouvelUtilisateurModal">
                <i class="fas fa-user-plus me-1"></i>Nouvel Utilisateur
            </button>
        </div>

        <!-- Statistiques rapides -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4>15</h4>
                                <p class="mb-0">Total utilisateurs</p>
                            </div>
                            <i class="fas fa-users fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4>12</h4>
                                <p class="mb-0">Utilisateurs actifs</p>
                            </div>
                            <i class="fas fa-user-check fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4>3</h4>
                                <p class="mb-0">Administrateurs</p>
                            </div>
                            <i class="fas fa-user-shield fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4>9</h4>
                                <p class="mb-0">Techniciens</p>
                            </div>
                            <i class="fas fa-hard-hat fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres et recherche -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control" placeholder="Rechercher un utilisateur..." id="searchInput">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="roleFilter">
                            <option value="">Tous les rôles</option>
                            <option value="admin">Administrateur</option>
                            <option value="technicien">Technicien</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="statusFilter">
                            <option value="">Tous les statuts</option>
                            <option value="actif">Actif</option>
                            <option value="inactif">Inactif</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-primary w-100" onclick="resetFilters()">
                            <i class="fas fa-undo me-1"></i>Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table des utilisateurs -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="utilisateursTable">
                    <thead class="table-light">
                        <tr>
                            <th>
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th>Utilisateur</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Statut</th>
                            <th>Dernière connexion</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input user-checkbox" data-id="1">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-primary text-white me-3">
                                        AB
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Ahmed BENALI</h6>
                                        <small class="text-muted">Technicien senior</small>
                                    </div>
                                </div>
                            </td>
                            <td>ahmed.benali@ormvat.ma</td>
                            <td>
                                <span class="badge bg-warning">Technicien</span>
                            </td>
                            <td>
                                <span class="badge bg-success">
                                    <i class="fas fa-circle me-1" style="font-size: 0.6rem;"></i>Actif
                                </span>
                            </td>
                            <td>
                                <small>Aujourd'hui, 14:30</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="voirUtilisateur(1)" title="Voir détails">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="modifierUtilisateur(1)" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="desactiverUtilisateur(1)" title="Désactiver">
                                        <i class="fas fa-user-slash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input user-checkbox" data-id="2">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-success text-white me-3">
                                        MT
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Mohammed TAZI</h6>
                                        <small class="text-muted">Administrateur système</small>
                                    </div>
                                </div>
                            </td>
                            <td>mohammed.tazi@ormvat.ma</td>
                            <td>
                                <span class="badge bg-danger">Administrateur</span>
                            </td>
                            <td>
                                <span class="badge bg-success">
                                    <i class="fas fa-circle me-1" style="font-size: 0.6rem;"></i>Actif
                                </span>
                            </td>
                            <td>
                                <small>Aujourd'hui, 16:45</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="voirUtilisateur(2)" title="Voir détails">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="modifierUtilisateur(2)" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-warning" onclick="resetPassword(2)" title="Reset mot de passe">
                                        <i class="fas fa-key"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input user-checkbox" data-id="3">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-info text-white me-3">
                                        FA
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Fatima ALAOUI</h6>
                                        <small class="text-muted">Technicien hydraulique</small>
                                    </div>
                                </div>
                            </td>
                            <td>fatima.alaoui@ormvat.ma</td>
                            <td>
                                <span class="badge bg-warning">Technicien</span>
                            </td>
                            <td>
                                <span class="badge bg-success">
                                    <i class="fas fa-circle me-1" style="font-size: 0.6rem;"></i>Actif
                                </span>
                            </td>
                            <td>
                                <small>Hier, 17:20</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="voirUtilisateur(3)" title="Voir détails">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="modifierUtilisateur(3)" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="desactiverUtilisateur(3)" title="Désactiver">
                                        <i class="fas fa-user-slash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input user-checkbox" data-id="4">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-secondary text-white me-3">
                                        LM
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Laila MANSOURI</h6>
                                        <small class="text-muted">Technicien formation</small>
                                    </div>
                                </div>
                            </td>
                            <td>laila.mansouri@ormvat.ma</td>
                            <td>
                                <span class="badge bg-warning">Technicien</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <i class="fas fa-circle me-1" style="font-size: 0.6rem;"></i>Inactif
                                </span>
                            </td>
                            <td>
                                <small>Il y a 3 jours</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="voirUtilisateur(4)" title="Voir détails">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="modifierUtilisateur(4)" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-success" onclick="activerUtilisateur(4)" title="Activer">
                                        <i class="fas fa-user-check"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
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
                    <a class="page-link" href="#">Suivant</a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Modal Nouvel Utilisateur -->
    <div class="modal fade" id="nouvelUtilisateurModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Créer un Nouvel Utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="/admin/utilisateurs" method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Prénom *</label>
                                    <input type="text" class="form-control" name="prenom" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nom *</label>
                                    <input type="text" class="form-control" name="nom" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" name="telephone" placeholder="+212 6XX XXX XXX">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Rôle *</label>
                                    <select class="form-select" name="role" required>
                                        <option value="">Sélectionner le rôle</option>
                                        <option value="admin">Administrateur</option>
                                        <option value="technicien">Technicien</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Statut</label>
                                    <select class="form-select" name="statut">
                                        <option value="actif">Actif</option>
                                        <option value="inactif">Inactif</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mot de passe temporaire *</label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="mot_de_passe" id="passwordInput" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                    <i class="fas fa-eye" id="passwordToggle"></i>
                                </button>
                                <button class="btn btn-outline-primary" type="button" onclick="generatePassword()">
                                    Générer
                                </button>
                            </div>
                            <div class="form-text">L'utilisateur devra changer ce mot de passe lors de sa première connexion</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Spécialité/Département</label>
                            <input type="text" class="form-control" name="specialite" placeholder="Ex: Hydraulique, Maintenance, Formation...">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Créer l'Utilisateur</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Détails Utilisateur -->
    <div class="modal fade" id="detailsUtilisateurModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Détails de l'Utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="utilisateur-details">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <div class="avatar-circle bg-primary text-white mx-auto mb-3" style="width: 100px; height: 100px; line-height: 100px; font-size: 2rem;">
                                    AB
                                </div>
                                <h5>Ahmed BENALI</h5>
                                <p class="text-muted">Technicien senior</p>
                                <span class="badge bg-success">Actif</span>
                            </div>
                            <div class="col-md-8">
                                <h6>Informations personnelles</h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td>ahmed.benali@ormvat.ma</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Téléphone:</strong></td>
                                        <td>+212 661 234 567</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Rôle:</strong></td>
                                        <td><span class="badge bg-warning">Technicien</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Date de création:</strong></td>
                                        <td>15 janvier 2024</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Dernière connexion:</strong></td>
                                        <td>Aujourd'hui à 14:30</td>
                                    </tr>
                                </table>

                                <h6 class="mt-4">Statistiques d'activité</h6>
                                <div class="row">
                                    <div class="col-md-4 text-center">
                                        <h4 class="text-primary">23</h4>
                                        <small>Tâches assignées</small>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <h4 class="text-success">18</h4>
                                        <small>Tâches terminées</small>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <h4 class="text-info">12</h4>
                                        <small>Rapports soumis</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-warning" onclick="modifierUtilisateur(1)">
                        <i class="fas fa-edit me-1"></i>Modifier
                    </button>
                    <button type="button" class="btn btn-info" onclick="resetPassword(1)">
                        <i class="fas fa-key me-1"></i>Reset Mot de Passe
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Styles CSS pour les avatars
        const style = document.createElement('style');
        style.textContent = `
            .avatar-circle {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                font-size: 14px;
            }
        `;
        document.head.appendChild(style);

        // Fonction pour sélectionner/désélectionner tous les utilisateurs
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.user-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Fonctions de gestion des utilisateurs
        function voirUtilisateur(id) {
            new bootstrap.Modal(document.getElementById('detailsUtilisateurModal')).show();
        }

        function modifierUtilisateur(id) {
            alert('Redirection vers la modification de l\'utilisateur ' + id);
        }

        function desactiverUtilisateur(id) {
            if (confirm('Êtes-vous sûr de vouloir désactiver cet utilisateur ?')) {
                alert('Utilisateur ' + id + ' désactivé');
            }
        }

        function activerUtilisateur(id) {
            if (confirm('Êtes-vous sûr de vouloir activer cet utilisateur ?')) {
                alert('Utilisateur ' + id + ' activé');
            }
        }

        function resetPassword(id) {
            if (confirm('Êtes-vous sûr de vouloir réinitialiser le mot de passe de cet utilisateur ?')) {
                alert('Mot de passe réinitialisé pour l\'utilisateur ' + id);
            }
        }

        // Fonction pour basculer la visibilité du mot de passe
        function togglePassword() {
            const passwordInput = document.getElementById('passwordInput');
            const passwordToggle = document.getElementById('passwordToggle');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordToggle.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                passwordToggle.className = 'fas fa-eye';
            }
        }

        // Fonction pour générer un mot de passe aléatoire
        function generatePassword() {
            const length = 12;
            const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#$%";
            let password = "";
            for (let i = 0; i < length; i++) {
                password += charset.charAt(Math.floor(Math.random() * charset.length));
            }
            document.getElementById('passwordInput').value = password;
        }

        // Fonction pour réinitialiser les filtres
        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('roleFilter').value = '';
            document.getElementById('statusFilter').value = '';
            // Réafficher toutes les lignes
        }

        // Filtrage en temps réel
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#utilisateursTable tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Filtres par rôle et statut
        ['roleFilter', 'statusFilter'].forEach(filterId => {
            document.getElementById(filterId).addEventListener('change', function() {
                // Logique de filtrage
                console.log('Filtre changé:', filterId, this.value);
            });
        });
    </script>
</body>
</html>
