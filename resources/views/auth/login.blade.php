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
            --ormvat-light: #a8d5ba;
        }

        body {
            background: linear-gradient(135deg, var(--ormvat-primary) 0%, var(--ormvat-secondary) 50%, var(--ormvat-accent) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            overflow: hidden;
        }

        .logo-section {
            background: linear-gradient(45deg, var(--ormvat-primary), var(--ormvat-secondary));
            color: white;
            position: relative;
            overflow: hidden;
        }

        .water-animation {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .wave {
            position: absolute;
            bottom: 0;
            width: 200%;
            height: 60px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 60px;
            animation: wave 6s ease-in-out infinite;
        }

        .wave:nth-child(2) {
            animation-delay: 1s;
            opacity: 0.7;
            height: 45px;
        }

        .wave:nth-child(3) {
            animation-delay: 2s;
            opacity: 0.5;
            height: 35px;
        }

        @keyframes wave {
            0%, 100% { transform: translateX(-50%) translateY(0px) rotate(0deg); }
            25% { transform: translateX(-50%) translateY(-8px) rotate(1deg); }
            50% { transform: translateX(-50%) translateY(-15px) rotate(-1deg); }
            75% { transform: translateX(-50%) translateY(-8px) rotate(0.5deg); }
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--ormvat-primary), var(--ormvat-secondary));
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            border-radius: 12px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(44, 85, 48, 0.3);
        }

        .form-control {
            border-radius: 12px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--ormvat-accent);
            box-shadow: 0 0 0 0.2rem rgba(127, 176, 105, 0.25);
        }

        .demo-card {
            transition: all 0.3s ease;
            cursor: pointer;
            border-radius: 12px;
        }

        .demo-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .feature-item {
            padding: 8px 0;
            opacity: 0;
            animation: fadeInUp 0.6s ease forwards;
        }

        .feature-item:nth-child(1) { animation-delay: 0.2s; }
        .feature-item:nth-child(2) { animation-delay: 0.4s; }
        .feature-item:nth-child(3) { animation-delay: 0.6s; }
        .feature-item:nth-child(4) { animation-delay: 0.8s; }

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

        .floating-icon {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        @media (max-width: 768px) {
            .logo-section {
                border-radius: 25px 25px 0 0;
            }
            .login-container {
                margin: 1rem;
                border-radius: 20px;
            }
            .feature-item {
                font-size: 0.9rem;
            }
        }

        .alert {
            border-radius: 12px;
            border: none;
        }

        .input-group-text {
            border-radius: 0 12px 12px 0;
            border: 2px solid #e9ecef;
            border-left: none;
            background: white;
        }

        .input-group .form-control {
            border-radius: 12px 0 0 12px;
            border-right: none;
        }

        .position-relative {
            z-index: 10;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <div class="card login-container border-0">
                    <div class="row g-0">
                        <!-- Section Logo et Information -->
                        <div class="col-md-6 logo-section d-flex flex-column justify-content-center align-items-center p-5 position-relative">
                            <div class="water-animation">
                                <div class="wave"></div>
                                <div class="wave"></div>
                                <div class="wave"></div>
                            </div>

                            <div class="text-center position-relative">
                                <div class="mb-4">
                                    <i class="fas fa-water display-1 mb-3 floating-icon"></i>
                                    <h2 class="fw-bold mb-2">PlanifTech</h2>
                                    <p class="fs-5 mb-4">Système de gestion technique ORMVAT</p>
                                </div>

                                <div class="text-start">
                                    <h5 class="mb-3">
                                        <i class="fas fa-building me-2"></i>
                                        Office Régional de Mise en Valeur Agricole du Tadla
                                    </h5>

                                    <div class="feature-item">
                                        <i class="fas fa-check-circle me-2 text-success"></i>
                                        <span>Gestion des tâches et projets</span>
                                    </div>
                                    <div class="feature-item">
                                        <i class="fas fa-check-circle me-2 text-success"></i>
                                        <span>Planification des interventions</span>
                                    </div>
                                    <div class="feature-item">
                                        <i class="fas fa-check-circle me-2 text-success"></i>
                                        <span>Rapports d'intervention détaillés</span>
                                    </div>
                                    <div class="feature-item">
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

                            <!-- Messages d'erreur et de succès -->
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

                            @if (session('error'))
                                <div class="alert alert-danger d-flex align-items-center" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <div>{{ session('error') }}</div>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('login') }}" id="loginForm">
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
                                        <input class="form-check-input" type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="remember">
                                            Se souvenir de moi
                                        </label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100 mb-3" id="submitBtn">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    Se connecter
                                </button>
                            </form>

                            <!-- Comptes de démonstration -->
                            <div class="border-top pt-4 mt-4">
                                <h6 class="text-muted mb-3 text-center">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Comptes de démonstration
                                </h6>
                                <div class="row g-2">
                                    <div class="col-12">
                                        <div class="card demo-card border-primary" data-email="admin@ormvat.ma" data-password="admin123">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <i class="fas fa-user-shield text-primary fa-2x"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="card-title mb-1 text-primary">Administrateur</h6>
                                                        <small class="text-muted">
                                                            <strong>Email:</strong> admin@ormvat.ma<br>
                                                            <strong>Mot de passe:</strong> admin123
                                                        </small>
                                                    </div>
                                                    <div>
                                                        <i class="fas fa-arrow-right text-primary"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="card demo-card border-success" data-email="technicien@ormvat.ma" data-password="tech123">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <i class="fas fa-user-cog text-success fa-2x"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="card-title mb-1 text-success">Technicien</h6>
                                                        <small class="text-muted">
                                                            <strong>Email:</strong> technicien@ormvat.ma<br>
                                                            <strong>Mot de passe:</strong> tech123
                                                        </small>
                                                    </div>
                                                    <div>
                                                        <i class="fas fa-arrow-right text-success"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-click me-1"></i>
                                        Cliquez sur une carte pour remplir automatiquement
                                    </small>
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
        <div class="text-white small">
            <i class="fas fa-question-circle me-1"></i>
            Besoin d'aide ?
            <a href="mailto:support@ormvat.ma" class="text-white">Contactez le support</a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
            const demoCards = document.querySelectorAll('.demo-card');
            demoCards.forEach(card => {
                card.addEventListener('click', function() {
                    const email = this.dataset.email;
                    const password = this.dataset.password;

                    document.getElementById('email').value = email;
                    document.getElementById('password').value = password;

                    // Effet visuel
                    this.style.transform = 'scale(0.98)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);

                    // Focus sur le bouton de connexion
                    setTimeout(() => {
                        document.getElementById('submitBtn').focus();
                    }, 200);
                });
            });

            // Animation de chargement au submit
            document.getElementById('loginForm').addEventListener('submit', function(e) {
                const submitBtn = document.getElementById('submitBtn');
                const originalText = submitBtn.innerHTML;

                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Connexion en cours...';
                submitBtn.disabled = true;

                // Restaurer si erreur après 5 secondes
                setTimeout(() => {
                    if (submitBtn.disabled) {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                }, 5000);
            });

            // Validation côté client
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');

            emailInput.addEventListener('blur', function() {
                if (this.value && !this.value.includes('@')) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            });

            passwordInput.addEventListener('input', function() {
                if (this.value.length > 0 && this.value.length < 6) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            });

            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Alt + 1 = Admin account
                if (e.altKey && e.key === '1') {
                    e.preventDefault();
                    document.querySelector('[data-email="admin@ormvat.ma"]').click();
                }

                // Alt + 2 = Technicien account
                if (e.altKey && e.key === '2') {
                    e.preventDefault();
                    document.querySelector('[data-email="technicien@ormvat.ma"]').click();
                }
            });
        });
    </script>
</body>
</html>
