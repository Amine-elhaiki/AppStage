<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Connexion - PlanifTech ORMVAT</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --ormvat-primary: #2c5530;
            --ormvat-secondary: #4a7c59;
            --ormvat-accent: #7fb069;
        }

        body {
            background: linear-gradient(135deg, var(--ormvat-primary) 0%, var(--ormvat-secondary) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--ormvat-primary), var(--ormvat-secondary));
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(44, 85, 48, 0.3);
        }

        .form-control:focus {
            border-color: var(--ormvat-accent);
            box-shadow: 0 0 0 0.2rem rgba(127, 176, 105, 0.25);
        }

        .logo-section {
            background: linear-gradient(45deg, var(--ormvat-primary), var(--ormvat-secondary));
            color: white;
            border-radius: 20px 0 0 20px;
        }

        .water-animation {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            border-radius: 20px 0 0 20px;
        }

        .wave {
            position: absolute;
            bottom: 0;
            width: 200%;
            height: 50px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50px;
            animation: wave 4s ease-in-out infinite;
        }

        .wave:nth-child(2) {
            animation-delay: 0.5s;
            opacity: 0.5;
        }

        @keyframes wave {
            0%, 100% { transform: translateX(-50%) translateY(0px); }
            50% { transform: translateX(-50%) translateY(-10px); }
        }

        @media (max-width: 768px) {
            .logo-section {
                border-radius: 20px 20px 0 0;
            }
            .login-card {
                margin: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="card login-card border-0">
                    <div class="row g-0">
                        <!-- Section Logo et Information -->
                        <div class="col-md-6 logo-section d-flex flex-column justify-content-center align-items-center p-5 position-relative">
                            <div class="water-animation">
                                <div class="wave"></div>
                                <div class="wave"></div>
                            </div>

                            <div class="text-center position-relative z-index-1">
                                <div class="mb-4">
                                    <i class="fas fa-water display-1 mb-3"></i>
                                    <h2 class="fw-bold mb-2">PlanifTech</h2>
                                    <p class="fs-5 mb-4">Système de gestion des interventions techniques</p>
                                </div>

                                <div class="text-start">
                                    <h5 class="mb-3">
                                        <i class="fas fa-building me-2"></i>
                                        Office Régional de Mise en Valeur Agricole du Tadla
                                    </h5>

                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-check-circle me-2 text-success"></i>
                                        <span>Gestion des tâches et projets</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-check-circle me-2 text-success"></i>
                                        <span>Planification des interventions</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-check-circle me-2 text-success"></i>
                                        <span>Rapports d'intervention</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check-circle me-2 text-success"></i>
                                        <span>Suivi en temps réel</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section Formulaire -->
                        <div class="col-md-6 p-5">
                            <div class="text-center mb-4">
                                <h3 class="fw-bold text-dark mb-2">Connexion</h3>
                                <p class="text-muted">Accédez à votre espace de travail</p>
                            </div>

                            <!-- Messages d'erreur -->
                            @if ($errors->any())
                                <div class="alert alert-danger d-flex align-items-center" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <div>
                                        @foreach ($errors->all() as $error)
                                            <div>{{ $error }}</div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if (session('success'))
                                <div class="alert alert-success d-flex align-items-center" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <div>{{ session('success') }}</div>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('login') }}">
                                @csrf

                                <div class="mb-3">
                                    <label for="email" class="form-label fw-semibold">
                                        <i class="fas fa-envelope me-1"></i>
                                        Adresse email
                                    </label>
                                    <input type="email"
                                           class="form-control form-control-lg @error('email') is-invalid @enderror"
                                           id="email"
                                           name="email"
                                           value="{{ old('email') }}"
                                           placeholder="votre.email@ormvat.ma"
                                           required
                                           autofocus>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="password" class="form-label fw-semibold">
                                        <i class="fas fa-lock me-1"></i>
                                        Mot de passe
                                    </label>
                                    <div class="input-group">
                                        <input type="password"
                                               class="form-control form-control-lg @error('password') is-invalid @enderror"
                                               id="password"
                                               name="password"
                                               placeholder="Votre mot de passe"
                                               required>
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye" id="togglePasswordIcon"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                        <label class="form-check-label" for="remember">
                                            Se souvenir de moi
                                        </label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    Se connecter
                                </button>
                            </form>

                            <!-- Comptes de démonstration -->
                            <div class="border-top pt-4 mt-4">
                                <h6 class="text-muted mb-3">Comptes de démonstration :</h6>
                                <div class="row g-2">
                                    <div class="col-12">
                                        <div class="card border-primary">
                                            <div class="card-body p-3">
                                                <h6 class="card-title mb-1">
                                                    <i class="fas fa-user-shield text-primary me-1"></i>
                                                    Administrateur
                                                </h6>
                                                <small class="text-muted">
                                                    <strong>Email:</strong> admin@ormvat.ma<br>
                                                    <strong>Mot de passe:</strong> admin123
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="card border-success">
                                            <div class="card-body p-3">
                                                <h6 class="card-title mb-1">
                                                    <i class="fas fa-user-cog text-success me-1"></i>
                                                    Technicien
                                                </h6>
                                                <small class="text-muted">
                                                    <strong>Email:</strong> technicien@ormvat.ma<br>
                                                    <strong>Mot de passe:</strong> tech123
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact et support -->
    <div class="position-fixed bottom-0 end-0 p-3">
        <div class="text-white-50 small">
            <i class="fas fa-question-circle me-1"></i>
            Besoin d'aide ?
            <a href="mailto:support@ormvat.ma" class="text-white">Contactez le support</a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('togglePasswordIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        });

        // Auto-fill demo accounts
        document.addEventListener('DOMContentLoaded', function() {
            const demoCards = document.querySelectorAll('.card.border-primary, .card.border-success');

            demoCards.forEach(card => {
                card.style.cursor = 'pointer';
                card.addEventListener('click', function() {
                    const isAdmin = this.classList.contains('border-primary');
                    const emailInput = document.getElementById('email');
                    const passwordInput = document.getElementById('password');

                    if (isAdmin) {
                        emailInput.value = 'admin@ormvat.ma';
                        passwordInput.value = 'admin123';
                    } else {
                        emailInput.value = 'technicien@ormvat.ma';
                        passwordInput.value = 'tech123';
                    }

                    // Effet visuel
                    this.classList.add('bg-light');
                    setTimeout(() => {
                        this.classList.remove('bg-light');
                    }, 200);
                });
            });
        });

        // Animation de chargement au submit
        document.querySelector('form').addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Connexion en cours...';
            submitBtn.disabled = true;

            // Si la connexion échoue, restaurer le bouton après 3 secondes
            setTimeout(() => {
                if (submitBtn.disabled) {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            }, 3000);
        });
    </script>
</body>
</html>
