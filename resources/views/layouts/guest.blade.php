<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'PlanifTech - ORMVAT')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @stack('styles')

    <style>
        /* Styles spécifiques pour les pages invités */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .bg-light {
            background: rgba(255, 255, 255, 0.1) !important;
        }

        .card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95) !important;
            border-radius: 15px;
        }

        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .text-primary {
            color: #667eea !important;
        }

        /* Animation de fond */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
            z-index: -1;
            animation: backgroundMove 20s linear infinite;
        }

        @keyframes backgroundMove {
            0% { transform: translateX(0) translateY(0); }
            100% { transform: translateX(-60px) translateY(-60px); }
        }

        /* Particules flottantes */
        .floating-particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .particle:nth-child(1) {
            left: 20%;
            animation-delay: 0s;
            width: 10px;
            height: 10px;
        }

        .particle:nth-child(2) {
            left: 50%;
            animation-delay: 1s;
            width: 15px;
            height: 15px;
        }

        .particle:nth-child(3) {
            left: 80%;
            animation-delay: 2s;
            width: 8px;
            height: 8px;
        }

        @keyframes float {
            0%, 100% { transform: translateY(100vh) rotate(0deg); }
            50% { transform: translateY(-10vh) rotate(180deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .card-body {
                padding: 2rem !important;
            }
        }

        /* Animation d'apparition */
        .fade-in {
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <!-- Particules d'arrière-plan -->
    <div class="floating-particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <div id="app" class="fade-in">
        <!-- Header avec informations ORMVAT -->
        <header class="text-center text-white py-3">
            <div class="container">
                <small class="opacity-75">
                    <i class="fas fa-shield-alt me-1"></i>
                    Système sécurisé - Office Régional de Mise en Valeur Agricole du Tadla
                </small>
            </div>
        </header>

        <!-- Contenu principal -->
        <main>
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="text-center text-white py-4 mt-5">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <p class="mb-2 opacity-75">
                            <strong>PlanifTech</strong> - Gestion des interventions techniques
                        </p>
                        <p class="small mb-0 opacity-50">
                            Développé pour l'ORMVAT • Version {{ config('app.version', '1.0') }} •
                            {{ date('Y') }}
                        </p>
                    </div>
                </div>

                <!-- Informations de contact d'urgence -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="small opacity-50">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            En cas de problème technique urgent, contactez le service informatique
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS pour les pages invités -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animation smooth des alertes
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert, index) {
                alert.style.animationDelay = (index * 0.1) + 's';
                alert.classList.add('fade-in');

                // Auto-dismiss après 6 secondes
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    if (bsAlert) {
                        bsAlert.close();
                    }
                }, 6000);
            });

            // Animation des champs de formulaire
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(function(input) {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.02)';
                    this.parentElement.style.transition = 'transform 0.2s ease';
                });

                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            });

            // Effet de particules supplémentaires au clic
            document.addEventListener('click', function(e) {
                createClickParticle(e.clientX, e.clientY);
            });

            function createClickParticle(x, y) {
                const particle = document.createElement('div');
                particle.style.position = 'fixed';
                particle.style.left = x + 'px';
                particle.style.top = y + 'px';
                particle.style.width = '6px';
                particle.style.height = '6px';
                particle.style.background = 'rgba(255, 255, 255, 0.6)';
                particle.style.borderRadius = '50%';
                particle.style.pointerEvents = 'none';
                particle.style.zIndex = '9999';
                particle.style.animation = 'clickParticle 0.6s ease-out forwards';

                document.body.appendChild(particle);

                setTimeout(() => {
                    particle.remove();
                }, 600);
            }

            // Ajouter l'animation pour les particules de clic
            const style = document.createElement('style');
            style.textContent = `
                @keyframes clickParticle {
                    0% {
                        transform: scale(1) translateY(0);
                        opacity: 1;
                    }
                    100% {
                        transform: scale(0) translateY(-20px);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        });
    </script>

    @stack('scripts')
</body>
</html>
