<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\Tache;
use App\Models\User;
use App\Models\Projet;
use App\Models\Evenement;
use Carbon\Carbon;

class TacheSeeder extends Seeder
{
    public function run(): void
    {
        $techniciens = User::where('role', 'technicien')->pluck('id');
        $projets = Projet::all()->pluck('id');
        $evenements = Evenement::all()->pluck('id');

        $taches = [
            // Tâches d'inspection
            [
                'titre' => 'Contrôle qualité eau irrigation Secteur A',
                'description' => 'Effectuer les prélèvements et analyses de qualité de l\'eau d\'irrigation dans le secteur A selon les protocoles établis.',
                'date_echeance' => Carbon::now()->addDays(2),
                'priorite' => 'haute',
                'statut' => 'a_faire',
                'progression' => 0,
            ],
            [
                'titre' => 'Vérification vannes distribution Zone Est',
                'description' => 'Inspection et test de fonctionnement de toutes les vannes de distribution de la zone Est.',
                'date_echeance' => Carbon::now()->addDays(5),
                'priorite' => 'moyenne',
                'statut' => 'en_cours',
                'progression' => 30,
            ],
            [
                'titre' => 'Relevé compteurs eau secteur Fkih Ben Salah',
                'description' => 'Effectuer la tournée mensuelle de relevé des compteurs d\'eau dans le secteur Fkih Ben Salah.',
                'date_echeance' => Carbon::now()->addDays(3),
                'priorite' => 'moyenne',
                'statut' => 'en_cours',
                'progression' => 60,
            ],

            // Tâches de maintenance
            [
                'titre' => 'Nettoyage filtres Station P8',
                'description' => 'Nettoyage et remplacement si nécessaire des filtres de la station de pompage P8.',
                'date_echeance' => Carbon::now()->addDays(1),
                'priorite' => 'haute',
                'statut' => 'a_faire',
                'progression' => 0,
            ],
            [
                'titre' => 'Lubrification équipements Station P12',
                'description' => 'Effectuer la lubrification programmée de tous les équipements mécaniques de la station P12.',
                'date_echeance' => Carbon::now()->addDays(7),
                'priorite' => 'moyenne',
                'statut' => 'a_faire',
                'progression' => 0,
            ],
            [
                'titre' => 'Réparation fuite Canal secondaire C4',
                'description' => 'Localiser et réparer la fuite signalée sur le canal secondaire C4.',
                'date_echeance' => Carbon::yesterday(), // Tâche en retard
                'priorite' => 'haute',
                'statut' => 'en_cours',
                'progression' => 80,
            ],

            // Tâches d'entretien
            [
                'titre' => 'Débroussaillage abords bassin B3',
                'description' => 'Entretien de la végétation aux abords du bassin de rétention B3.',
                'date_echeance' => Carbon::now()->addWeek(),
                'priorite' => 'basse',
                'statut' => 'a_faire',
                'progression' => 0,
            ],
            [
                'titre' => 'Calibrage capteurs niveau d\'eau',
                'description' => 'Vérification et calibrage de tous les capteurs de niveau d\'eau du secteur Nord.',
                'date_echeance' => Carbon::now()->addDays(10),
                'priorite' => 'moyenne',
                'statut' => 'a_faire',
                'progression' => 0,
            ],

            // Tâches terminées
            [
                'titre' => 'Installation nouveau compteur Zone B',
                'description' => 'Installation du nouveau compteur électronique dans la zone B comme prévu au planning.',
                'date_echeance' => Carbon::now()->subDays(2),
                'priorite' => 'moyenne',
                'statut' => 'termine',
                'progression' => 100,
            ],
            [
                'titre' => 'Test système alarme Station P5',
                'description' => 'Test complet du système d\'alarme et de sécurité de la station P5.',
                'date_echeance' => Carbon::now()->subDays(5),
                'priorite' => 'haute',
                'statut' => 'termine',
                'progression' => 100,
            ],

            // Tâches liées aux projets
            [
                'titre' => 'Étude faisabilité extension Canal principal',
                'description' => 'Réaliser l\'étude de faisabilité pour l\'extension du canal principal vers la zone Sud.',
                'date_echeance' => Carbon::now()->addDays(14),
                'priorite' => 'haute',
                'statut' => 'en_cours',
                'progression' => 25,
            ],
            [
                'titre' => 'Préparation site installation pompe',
                'description' => 'Préparation du site pour l\'installation de la nouvelle pompe haute pression.',
                'date_echeance' => Carbon::now()->addDays(12),
                'priorite' => 'moyenne',
                'statut' => 'a_faire',
                'progression' => 0,
            ],
        ];

        foreach ($taches as $tache) {
            Tache::create([
                'titre' => $tache['titre'],
                'description' => $tache['description'],
                'date_echeance' => $tache['date_echeance'],
                'priorite' => $tache['priorite'],
                'statut' => $tache['statut'],
                'progression' => $tache['progression'],
                'id_utilisateur' => $techniciens->random(),
                'id_projet' => rand(0, 1) ? $projets->random() : null,
                'id_evenement' => rand(0, 1) ? $evenements->random() : null,
            ]);
        }
    }
}
