<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\Event;
use App\Models\Report;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Créer les utilisateurs de base
        $this->createUsers();

        // Créer les projets
        $this->createProjects();

        // Créer les tâches
        $this->createTasks();

        // Créer les événements
        $this->createEvents();

        // Créer les rapports
        $this->createReports();
    }

    private function createUsers()
    {
        // Administrateur principal
        User::create([
            'nom' => 'ALAMI',
            'prenom' => 'Ahmed',
            'email' => 'admin@ormvat.ma',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'telephone' => '+212 523 123 456',
            'specialite' => 'Administration système',
            'statut' => 'actif',
            'permissions' => ['all'],
            'derniere_connexion' => now(),
        ]);

        // Chef d'équipe
        User::create([
            'nom' => 'BENJELLOUN',
            'prenom' => 'Fatima',
            'email' => 'chef@ormvat.ma',
            'password' => Hash::make('chef123'),
            'role' => 'chef_equipe',
            'telephone' => '+212 523 234 567',
            'specialite' => 'Gestion d\'équipe',
            'statut' => 'actif',
            'permissions' => ['manage_team', 'validate_reports'],
            'derniere_connexion' => now()->subHours(2),
        ]);

        // Technicien principal (compte de démonstration)
        User::create([
            'nom' => 'IDRISSI',
            'prenom' => 'Mohamed',
            'email' => 'technicien@ormvat.ma',
            'password' => Hash::make('tech123'),
            'role' => 'technicien',
            'telephone' => '+212 523 345 678',
            'specialite' => 'Irrigation et drainage',
            'statut' => 'actif',
            'permissions' => ['create_reports', 'manage_tasks'],
            'derniere_connexion' => now()->subMinutes(30),
        ]);

        // Autres techniciens
        $techniciens = [
            [
                'nom' => 'CHERKAOUI',
                'prenom' => 'Aicha',
                'email' => 'aicha.cherkaoui@ormvat.ma',
                'specialite' => 'Maintenance équipements',
            ],
            [
                'nom' => 'BENNANI',
                'prenom' => 'Youssef',
                'email' => 'youssef.bennani@ormvat.ma',
                'specialite' => 'Infrastructure hydraulique',
            ],
            [
                'nom' => 'TAZI',
                'prenom' => 'Khadija',
                'email' => 'khadija.tazi@ormvat.ma',
                'specialite' => 'Contrôle qualité eau',
            ],
            [
                'nom' => 'BERRADA',
                'prenom' => 'Omar',
                'email' => 'omar.berrada@ormvat.ma',
                'specialite' => 'Électromécanique',
            ],
            [
                'nom' => 'HAMDI',
                'prenom' => 'Zineb',
                'email' => 'zineb.hamdi@ormvat.ma',
                'specialite' => 'Topographie',
            ],
        ];

        foreach ($techniciens as $technicien) {
            User::create([
                'nom' => $technicien['nom'],
                'prenom' => $technicien['prenom'],
                'email' => $technicien['email'],
                'password' => Hash::make('password123'),
                'role' => 'technicien',
                'telephone' => '+212 523 ' . rand(100, 999) . ' ' . rand(100, 999),
                'specialite' => $technicien['specialite'],
                'statut' => 'actif',
                'permissions' => ['create_reports', 'manage_tasks'],
                'derniere_connexion' => now()->subHours(rand(1, 48)),
            ]);
        }
    }

    private function createProjects()
    {
        $admin = User::where('role', 'admin')->first();
        $chefEquipe = User::where('role', 'chef_equipe')->first();
        $techniciens = User::where('role', 'technicien')->get();

        $projets = [
            [
                'nom' => 'Modernisation réseau irrigation Béni Mellal',
                'description' => 'Modernisation complète du système d\'irrigation de la zone de Béni Mellal avec installation de nouveaux équipements de pompage et rénovation des canaux principaux.',
                'statut' => 'en_cours',
                'priorite' => 'haute',
                'date_debut' => now()->subMonths(2),
                'date_fin' => now()->addMonths(4),
                'budget' => 2500000.00,
                'zone_geographique' => 'Béni Mellal Centre',
                'pourcentage_avancement' => 35,
                'responsable_id' => $chefEquipe->id,
            ],
            [
                'nom' => 'Réhabilitation station pompage Kasba Tadla',
                'description' => 'Réhabilitation complète de la station de pompage principale de Kasba Tadla incluant le remplacement des pompes et la modernisation du système de contrôle.',
                'statut' => 'en_cours',
                'priorite' => 'urgente',
                'date_debut' => now()->subMonth(),
                'date_fin' => now()->addMonths(2),
                'budget' => 1800000.00,
                'zone_geographique' => 'Kasba Tadla',
                'pourcentage_avancement' => 60,
                'responsable_id' => $techniciens->random()->id,
            ],
            [
                'nom' => 'Extension réseau distribution Fquih Ben Salah',
                'description' => 'Extension du réseau de distribution d\'eau pour couvrir les nouvelles zones agricoles de Fquih Ben Salah.',
                'statut' => 'planifie',
                'priorite' => 'moyenne',
                'date_debut' => now()->addMonth(),
                'date_fin' => now()->addMonths(6),
                'budget' => 3200000.00,
                'zone_geographique' => 'Fquih Ben Salah',
                'pourcentage_avancement' => 0,
                'responsable_id' => $techniciens->random()->id,
            ],
            [
                'nom' => 'Maintenance préventive infrastructure 2024',
                'description' => 'Programme de maintenance préventive de toute l\'infrastructure hydraulique de la région pour l\'année 2024.',
                'statut' => 'en_cours',
                'priorite' => 'normale',
                'date_debut' => now()->startOfYear(),
                'date_fin' => now()->endOfYear(),
                'budget' => 800000.00,
                'zone_geographique' => 'Région Tadla',
                'pourcentage_avancement' => 45,
                'responsable_id' => $admin->id,
            ],
            [
                'nom' => 'Installation capteurs IoT réseau irrigation',
                'description' => 'Installation d\'un système de capteurs IoT pour le monitoring en temps réel du réseau d\'irrigation.',
                'statut' => 'termine',
                'priorite' => 'haute',
                'date_debut' => now()->subMonths(6),
                'date_fin' => now()->subMonth(),
                'budget' => 650000.00,
                'zone_geographique' => 'Multiple',
                'pourcentage_avancement' => 100,
                'responsable_id' => $techniciens->random()->id,
            ],
        ];

        foreach ($projets as $projet) {
            Project::create($projet);
        }
    }

    private function createTasks()
    {
        $users = User::all();
        $projects = Project::all();

        $taches = [
            // Tâches pour le projet de modernisation Béni Mellal
            [
                'titre' => 'Inspection des canaux principaux',
                'description' => 'Inspection complète de l\'état des canaux principaux et identification des zones nécessitant une réparation.',
                'statut' => 'termine',
                'priorite' => 'haute',
                'date_creation' => now()->subDays(20),
                'date_echeance' => now()->subDays(15),
                'date_debut_reelle' => now()->subDays(19),
                'date_fin_reelle' => now()->subDays(16),
                'progression' => 100,
                'commentaires' => 'Inspection terminée. Rapport détaillé disponible.',
                'project_id' => 1,
            ],
            [
                'titre' => 'Installation nouvelles pompes secteur Nord',
                'description' => 'Installation et mise en service de 3 nouvelles pompes haute capacité dans le secteur Nord.',
                'statut' => 'en_cours',
                'priorite' => 'haute',
                'date_creation' => now()->subDays(10),
                'date_echeance' => now()->addDays(15),
                'date_debut_reelle' => now()->subDays(5),
                'progression' => 70,
                'commentaires' => '2 pompes installées, reste la troisième.',
                'project_id' => 1,
            ],
            [
                'titre' => 'Tests hydrauliques réseau rénové',
                'description' => 'Effectuer les tests de pression et de débit sur les sections rénovées du réseau.',
                'statut' => 'a_faire',
                'priorite' => 'moyenne',
                'date_creation' => now()->subDays(5),
                'date_echeance' => now()->addDays(20),
                'progression' => 0,
                'project_id' => 1,
            ],

            // Tâches pour la station de Kasba Tadla
            [
                'titre' => 'Démontage anciennes pompes',
                'description' => 'Démontage sécurisé des anciennes pompes de la station principale.',
                'statut' => 'termine',
                'priorite' => 'urgente',
                'date_creation' => now()->subDays(25),
                'date_echeance' => now()->subDays(20),
                'date_debut_reelle' => now()->subDays(24),
                'date_fin_reelle' => now()->subDays(21),
                'progression' => 100,
                'commentaires' => 'Démontage effectué sans incident.',
                'project_id' => 2,
            ],
            [
                'titre' => 'Installation système de contrôle automatisé',
                'description' => 'Installation et configuration du nouveau système de contrôle automatisé de la station.',
                'statut' => 'en_cours',
                'priorite' => 'urgente',
                'date_creation' => now()->subDays(15),
                'date_echeance' => now()->addDays(10),
                'date_debut_reelle' => now()->subDays(10),
                'progression' => 80,
                'commentaires' => 'Installation hardware terminée, configuration en cours.',
                'project_id' => 2,
            ],
            [
                'titre' => 'Formation équipe maintenance',
                'description' => 'Formation de l\'équipe de maintenance sur les nouveaux équipements installés.',
                'statut' => 'a_faire',
                'priorite' => 'moyenne',
                'date_creation' => now()->subDays(3),
                'date_echeance' => now()->addDays(25),
                'progression' => 0,
                'project_id' => 2,
            ],

            // Tâches générales
            [
                'titre' => 'Maintenance mensuelle pompe P-125',
                'description' => 'Maintenance préventive mensuelle de la pompe P-125 selon le planning.',
                'statut' => 'a_faire',
                'priorite' => 'normale',
                'date_creation' => now()->subDays(2),
                'date_echeance' => now()->addDays(5),
                'progression' => 0,
                'commentaires' => 'Prévoir arrêt de 4h pour maintenance.',
                'project_id' => 4,
            ],
            [
                'titre' => 'Réparation fuite canal C-45',
                'description' => 'Réparation urgente d\'une fuite détectée sur le canal C-45.',
                'statut' => 'en_cours',
                'priorite' => 'urgente',
                'date_creation' => now()->subDays(1),
                'date_echeance' => now()->addDays(2),
                'date_debut_reelle' => now(),
                'progression' => 30,
                'commentaires' => 'Équipe sur site, matériel en cours d\'acheminement.',
                'project_id' => 4,
            ],
            [
                'titre' => 'Calibrage capteurs secteur Est',
                'description' => 'Calibrage et vérification des capteurs de pression du secteur Est.',
                'statut' => 'a_faire',
                'priorite' => 'basse',
                'date_creation' => now()->subDays(7),
                'date_echeance' => now()->addDays(30),
                'progression' => 0,
                'project_id' => 5,
            ],
        ];

        foreach ($taches as $index => $tache) {
            // Assigner aléatoirement à un technicien ou au chef d'équipe
            $assignee = $users->where('role', '!=', 'admin')->random();
            $creator = $users->where('role', 'admin')->first();

            Task::create(array_merge($tache, [
                'user_id' => $assignee->id,
                'created_by' => $creator->id,
            ]));
        }
    }

    private function createEvents()
    {
        $users = User::all();
        $projects = Project::all();

        $evenements = [
            // Événements passés
            [
                'titre' => 'Réunion planning projet Béni Mellal',
                'description' => 'Réunion de planification et suivi du projet de modernisation de Béni Mellal.',
                'type' => 'reunion',
                'statut' => 'termine',
                'date_debut' => now()->subDays(10)->setTime(9, 0),
                'date_fin' => now()->subDays(10)->setTime(11, 0),
                'lieu' => 'Salle de réunion ORMVAT',
                'participants' => 'Équipe projet, responsables techniques',
                'materiels_requis' => 'Projecteur, plans techniques',
                'resultats' => 'Planning validé, prochaine étape définie.',
                'project_id' => 1,
            ],
            [
                'titre' => 'Intervention maintenance pompe P-89',
                'description' => 'Maintenance préventive programmée de la pompe P-89.',
                'type' => 'intervention',
                'statut' => 'termine',
                'date_debut' => now()->subDays(5)->setTime(8, 0),
                'date_fin' => now()->subDays(5)->setTime(12, 0),
                'lieu' => 'Station pompage Nord',
                'participants' => 'Équipe maintenance',
                'materiels_requis' => 'Outils spécialisés, pièces de rechange',
                'resultats' => 'Maintenance effectuée avec succès.',
                'project_id' => 4,
            ],

            // Événements du jour
            [
                'titre' => 'Inspection quotidienne réseau principal',
                'description' => 'Inspection de routine du réseau principal d\'irrigation.',
                'type' => 'visite',
                'statut' => 'planifie',
                'date_debut' => now()->setTime(7, 30),
                'date_fin' => now()->setTime(11, 30),
                'lieu' => 'Réseau principal Tadla',
                'participants' => 'Technicien de service',
                'materiels_requis' => 'Véhicule, équipement de mesure',
                'project_id' => 4,
            ],
            [
                'titre' => 'Formation utilisation nouveaux capteurs',
                'description' => 'Session de formation sur l\'utilisation des nouveaux capteurs IoT.',
                'type' => 'formation',
                'statut' => 'planifie',
                'date_debut' => now()->setTime(14, 0),
                'date_fin' => now()->setTime(17, 0),
                'lieu' => 'Centre de formation ORMVAT',
                'participants' => 'Techniciens terrain',
                'materiels_requis' => 'Capteurs de démonstration, manuel',
                'project_id' => 5,
            ],

            // Événements futurs
            [
                'titre' => 'Test mise en service station Kasba Tadla',
                'description' => 'Tests finaux et mise en service de la station rénovée de Kasba Tadla.',
                'type' => 'intervention',
                'statut' => 'planifie',
                'date_debut' => now()->addDays(7)->setTime(8, 0),
                'date_fin' => now()->addDays(7)->setTime(18, 0),
                'lieu' => 'Station pompage Kasba Tadla',
                'participants' => 'Équipe technique complète',
                'materiels_requis' => 'Équipement de test, instrumentation',
                'project_id' => 2,
            ],
            [
                'titre' => 'Réunion bilan mensuel',
                'description' => 'Réunion mensuelle de bilan des activités et planification.',
                'type' => 'reunion',
                'statut' => 'planifie',
                'date_debut' => now()->addDays(15)->setTime(9, 0),
                'date_fin' => now()->addDays(15)->setTime(12, 0),
                'lieu' => 'Salle de conférence ORMVAT',
                'participants' => 'Toute l\'équipe',
                'materiels_requis' => 'Présentations, rapports mensuels',
            ],
        ];

        foreach ($evenements as $evenement) {
            // Assigner aléatoirement à un utilisateur
            $assignee = $users->random();

            Event::create(array_merge($evenement, [
                'user_id' => $assignee->id,
            ]));
        }
    }

    private function createReports()
    {
        $users = User::where('role', '!=', 'admin')->get();
        $events = Event::all();
        $projects = Project::all();

        $rapports = [
            [
                'titre' => 'Rapport inspection canaux Béni Mellal',
                'description' => 'Rapport détaillé de l\'inspection des canaux principaux de Béni Mellal effectuée dans le cadre du projet de modernisation.',
                'type' => 'inspection',
                'date_intervention' => now()->subDays(16),
                'lieu' => 'Canaux principaux Béni Mellal',
                'probleme_identifie' => 'Fissures détectées sur 3 sections, sédimentation excessive dans le canal C-12.',
                'actions_effectuees' => 'Inspection visuelle complète, mesures topographiques, prélèvements d\'échantillons, documentation photographique.',
                'materiels_utilises' => 'Théodolite, niveau laser, appareil photo, kit de prélèvement',
                'etat_equipement' => 'moyen',
                'recommandations' => 'Réparation urgente des fissures, nettoyage du canal C-12, renforcement préventif des sections fragilisées.',
                'cout_intervention' => 450.00,
                'statut' => 'valide',
                'project_id' => 1,
                'event_id' => null,
            ],
            [
                'titre' => 'Rapport maintenance pompe P-89',
                'description' => 'Rapport de maintenance préventive de la pompe P-89 selon planning annuel.',
                'type' => 'maintenance',
                'date_intervention' => now()->subDays(5),
                'lieu' => 'Station pompage Nord',
                'probleme_identifie' => 'Usure normale des joints, vibrations légères détectées.',
                'actions_effectuees' => 'Remplacement joints d\'étanchéité, équilibrage rotor, vérification alignement, test de performance.',
                'materiels_utilises' => 'Joints neufs, équipement d\'équilibrage, outils de mesure vibrations',
                'etat_equipement' => 'bon',
                'recommandations' => 'Surveillance accrue des vibrations, prochaine maintenance dans 6 mois.',
                'cout_intervention' => 280.00,
                'statut' => 'valide',
                'project_id' => 4,
                'event_id' => 2,
            ],
            [
                'titre' => 'Rapport intervention urgente fuite C-45',
                'description' => 'Intervention d\'urgence pour réparation de fuite sur le canal C-45.',
                'type' => 'reparation',
                'date_intervention' => now(),
                'lieu' => 'Canal C-45, PK 12+500',
                'probleme_identifie' => 'Fuite importante due à la rupture d\'un joint de dilatation.',
                'actions_effectuees' => 'Arrêt d\'urgence du débit, évacuation de l\'eau, remplacement du joint défaillant, remise en service progressive.',
                'materiels_utilises' => 'Joint de dilatation neuf, étanchéité temporaire, pompe d\'évacuation',
                'etat_equipement' => 'bon',
                'recommandations' => 'Surveillance renforcée des autres joints, inspection préventive trimestrielle.',
                'cout_intervention' => 125.00,
                'statut' => 'soumis',
                'project_id' => 4,
                'event_id' => null,
            ],
            [
                'titre' => 'Rapport installation capteurs IoT zone Est',
                'description' => 'Installation et configuration des nouveaux capteurs IoT dans la zone Est.',
                'type' => 'installation',
                'date_intervention' => now()->subMonth(),
                'lieu' => 'Zone irrigation Est',
                'probleme_identifie' => null,
                'actions_effectuees' => 'Installation de 15 capteurs de pression, configuration réseau communication, tests de transmission, formation équipe locale.',
                'materiels_utilises' => 'Capteurs IoT, équipement réseau, outils d\'installation',
                'etat_equipement' => 'bon',
                'recommandations' => 'Monitoring quotidien pendant la première semaine, ajustements des seuils d\'alerte.',
                'cout_intervention' => 850.00,
                'statut' => 'valide',
                'project_id' => 5,
                'event_id' => null,
            ],
            [
                'titre' => 'Rapport inspection périodique station Kasba Tadla',
                'description' => 'Inspection périodique de la station de pompage de Kasba Tadla en cours de rénovation.',
                'type' => 'inspection',
                'date_intervention' => now()->subDays(3),
                'lieu' => 'Station pompage Kasba Tadla',
                'probleme_identifie' => 'Retard dans livraison de certains composants électroniques.',
                'actions_effectuees' => 'Inspection avancement travaux, vérification conformité installation, coordination avec fournisseurs.',
                'materiels_utilises' => 'Équipement de mesure, checklist inspection',
                'etat_equipement' => 'en_cours_renovation',
                'recommandations' => 'Accélérer livraison composants manquants, ajuster planning si nécessaire.',
                'cout_intervention' => 0.00,
                'statut' => 'brouillon',
                'project_id' => 2,
                'event_id' => null,
            ],
        ];

        foreach ($rapports as $rapport) {
            // Assigner aléatoirement à un technicien
            $author = $users->random();

            Report::create(array_merge($rapport, [
                'user_id' => $author->id,
            ]));
        }
    }
}
