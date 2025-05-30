<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admins = User::where('role', 'admin')->pluck('id')->toArray();
        $technicians = User::where('role', 'technicien')->pluck('id')->toArray();
        $allUsers = array_merge($admins, $technicians);

        // Projets de l'ORMVAT
        $projects = [
            [
                'nom' => 'Modernisation du réseau d\'irrigation Tadla Nord',
                'description' => 'Projet de modernisation et d\'automatisation du réseau d\'irrigation dans la zone Nord du Tadla. Comprend l\'installation de nouveaux systèmes de contrôle, la réhabilitation des canaux principaux et la mise en place de stations de pompage automatisées.',
                'date_debut' => Carbon::now()->subMonths(6),
                'date_fin' => Carbon::now()->addMonths(6),
                'zone_geographique' => 'Tadla Nord - Fkih Ben Salah',
                'statut' => 'en_cours',
                'id_responsable' => $allUsers[array_rand($allUsers)]
            ],
            [
                'nom' => 'Réhabilitation des stations de pompage Béni Mellal',
                'description' => 'Rénovation complète des 12 stations de pompage de la région de Béni Mellal. Remplacement des pompes, modernisation des systèmes électriques et installation de dispositifs de télémétrie.',
                'date_debut' => Carbon::now()->subMonths(3),
                'date_fin' => Carbon::now()->addMonths(9),
                'zone_geographique' => 'Béni Mellal - Secteur Est',
                'statut' => 'en_cours',
                'id_responsable' => $allUsers[array_rand($allUsers)]
            ],
            [
                'nom' => 'Contrôle qualité eau d\'irrigation 2024',
                'description' => 'Programme annuel de contrôle de la qualité de l\'eau d\'irrigation sur l\'ensemble du périmètre ORMVAT. Analyses physico-chimiques et biologiques, prélèvements, rapports de conformité.',
                'date_debut' => Carbon::now()->startOfYear(),
                'date_fin' => Carbon::now()->endOfYear(),
                'zone_geographique' => 'Ensemble du périmètre ORMVAT',
                'statut' => 'en_cours',
                'id_responsable' => $allUsers[array_rand($allUsers)]
            ],
            [
                'nom' => 'Installation capteurs météo intelligents',
                'description' => 'Déploiement d\'un réseau de capteurs météorologiques connectés pour optimiser la gestion de l\'irrigation. 25 stations automatiques avec transmission de données en temps réel.',
                'date_debut' => Carbon::now()->addMonths(1),
                'date_fin' => Carbon::now()->addMonths(8),
                'zone_geographique' => 'Tadla - Zone de montagne',
                'statut' => 'planifie',
                'id_responsable' => $allUsers[array_rand($allUsers)]
            ],
            [
                'nom' => 'Maintenance préventive infrastructure 2024',
                'description' => 'Programme de maintenance préventive annuelle de toute l\'infrastructure hydraulique : canaux, ouvrages d\'art, équipements de mesure, systèmes de contrôle.',
                'date_debut' => Carbon::now()->subMonths(2),
                'date_fin' => Carbon::now()->addMonths(10),
                'zone_geographique' => 'Ensemble du périmètre',
                'statut' => 'en_cours',
                'id_responsable' => $allUsers[array_rand($allUsers)]
            ],
            [
                'nom' => 'Digitalisation gestion parcellaire',
                'description' => 'Mise en place d\'un système digital de gestion des parcelles agricoles avec géolocalisation, suivi des cultures et optimisation des besoins en eau.',
                'date_debut' => Carbon::now()->addMonths(2),
                'date_fin' => Carbon::now()->addYear(),
                'zone_geographique' => 'Secteur Kasba Tadla',
                'statut' => 'planifie',
                'id_responsable' => $allUsers[array_rand($allUsers)]
            ],
            [
                'nom' => 'Rénovation réseau électrique stations',
                'description' => 'Mise à niveau du réseau électrique alimentant les stations de pompage et les postes de contrôle. Installation de nouvelles armoires électriques et systèmes de protection.',
                'date_debut' => Carbon::now()->subMonths(8),
                'date_fin' => Carbon::now()->subMonths(1),
                'zone_geographique' => 'Tadla Centre',
                'statut' => 'termine',
                'id_responsable' => $allUsers[array_rand($allUsers)]
            ],
            [
                'nom' => 'Formation techniciens nouvelles technologies',
                'description' => 'Programme de formation du personnel technique aux nouvelles technologies de l\'irrigation: automatisation, télémétrie, maintenance prédictive.',
                'date_debut' => Carbon::now()->addMonths(1),
                'date_fin' => Carbon::now()->addMonths(4),
                'zone_geographique' => 'Centre de formation ORMVAT',
                'statut' => 'planifie',
                'id_responsable' => $allUsers[array_rand($allUsers)]
            ],
            [
                'nom' => 'Étude impact changement climatique',
                'description' => 'Étude approfondie de l\'impact du changement climatique sur les ressources en eau et adaptation des stratégies d\'irrigation.',
                'date_debut' => Carbon::now()->subMonths(4),
                'date_fin' => Carbon::now()->addMonths(8),
                'zone_geographique' => 'Bassin versant Oum Er-Rbia',
                'statut' => 'en_cours',
                'id_responsable' => $allUsers[array_rand($allUsers)]
            ],
            [
                'nom' => 'Optimisation consommation énergétique',
                'description' => 'Projet d\'optimisation de la consommation énergétique des installations de pompage par l\'installation de variateurs de vitesse et l\'amélioration des rendements.',
                'date_debut' => Carbon::now()->addMonths(3),
                'date_fin' => Carbon::now()->addYear()->addMonths(2),
                'zone_geographique' => 'Ensemble des stations de pompage',
                'statut' => 'planifie',
                'id_responsable' => $allUsers[array_rand($allUsers)]
            ]
        ];

        foreach ($projects as $projectData) {
            Project::create($projectData);
        }

        $this->command->info('Projets créés avec succès!');
    }
}
