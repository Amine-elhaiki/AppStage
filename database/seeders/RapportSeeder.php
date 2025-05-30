<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rapport;
use App\Models\User;
use App\Models\Tache;
use App\Models\Evenement;
use Carbon\Carbon;

class RapportSeeder extends Seeder
{
    public function run(): void
    {
        $utilisateurs = User::all()->pluck('id');
        $taches = Tache::all()->pluck('id');
        $evenements = Evenement::all()->pluck('id');

        $rapports = [
            [
                'titre' => 'Intervention Station Pompage P12 - Maintenance Préventive',
                'date_intervention' => Carbon::now()->subDays(2),
                'lieu' => 'Station de Pompage P12',
                'type_intervention' => 'Maintenance Préventive',
                'actions' => 'Remplacement des filtres à huile, vérification des niveaux, contrôle des pressions, test du système d\'alarme, nettoyage des équipements.',
                'resultats' => 'Tous les paramètres sont conformes aux spécifications. Pression nominale rétablie à 4.2 bars. Système d\'alarme fonctionnel.',
                'problemes' => 'Usure prématurée du joint d\'étanchéité de la pompe principale. Vibrations légères détectées sur le moteur.',
                'recommandations' => 'Programmer le remplacement du joint dans les 2 semaines. Effectuer un contrôle d\'alignement du moteur lors de la prochaine maintenance.',
            ],
            [
                'titre' => 'Inspection Canal Principal Secteur B4',
                'date_intervention' => Carbon::now()->subDays(5),
                'lieu' => 'Canal Principal B4 - Km 2.5 à 8.3',
                'type_intervention' => 'Inspection Technique',
                'actions' => 'Inspection visuelle complète du canal, mesure des débits, contrôle de l\'étanchéité, vérification des ouvrages de régulation.',
                'resultats' => 'Canal en bon état général. Débit mesuré: 2.8 m³/s (conforme). 3 fissures mineures identifiées.',
                'problemes' => 'Présence de végétation aquatique excessive au niveau du PK 4.2. Légère érosion des berges au PK 6.1.',
                'recommandations' => 'Planifier un débroussaillage au PK 4.2. Renforcer la protection des berges au PK 6.1 avec des enrochements.',
            ],
            [
                'titre' => 'Réparation Urgente Vanne V15 Fkih Ben Salah',
                'date_intervention' => Carbon::now()->subDays(1),
                'lieu' => 'Secteur Fkih Ben Salah - Vanne V15',
                'type_intervention' => 'Réparation d\'Urgence',
                'actions' => 'Diagnostic de la panne, remplacement de la vanne défaillante, test de fonctionnement, mise en service.',
                'resultats' => 'Vanne remplacée avec succès. Fonctionnement normal rétabli. Distribution d\'eau normalisée pour 450 hectares.',
                'problemes' => 'Corrosion avancée de l\'ancienne vanne due à l\'âge (15 ans). Retard dans l\'intervention en raison de la disponibilité des pièces.',
                'recommandations' => 'Constituer un stock de vannes de rechange pour ce type d\'équipement. Programmer le remplacement préventif des vannes de même génération.',
            ],
            [
                'titre' => 'Contrôle Qualité Eau Irrigation - Secteur A',
                'date_intervention' => Carbon::now()->subDays(3),
                'lieu' => 'Points de prélèvement A1, A3, A5, A7',
                'type_intervention' => 'Contrôle Qualité',
                'actions' => 'Prélèvement d\'échantillons d\'eau, mesures in-situ (pH, conductivité, oxygène dissous), envoi au laboratoire pour analyses complètes.',
                'resultats' => 'pH: 7.2-7.8 (normal), Conductivité: 580-620 µS/cm (acceptable), Oxygène dissous: >6 mg/L (bon).',
                'problemes' => 'Légère augmentation de la turbidité au point A5. Présence de résidus organiques au point A7.',
                'recommandations' => 'Investiguer la source de turbidité au point A5. Nettoyer les grilles de filtration en amont du point A7.',
            ],
            [
                'titre' => 'Installation Capteur Télémétrie Station P8',
                'date_intervention' => Carbon::now()->subDays(7),
                'lieu' => 'Station de Pompage P8',
                'type_intervention' => 'Installation Équipement',
                'actions' => 'Installation du nouveau capteur de télémétrie, configuration du système, test de transmission des données, formation de l\'opérateur.',
                'resultats' => 'Capteur installé et fonctionnel. Transmission des données opérationnelle. Interface utilisateur configurée.',
                'problemes' => 'Signal GSM faible nécessitant l\'installation d\'une antenne externe. Problème de compatibilité mineur avec l\'ancien système.',
                'recommandations' => 'Installer une antenne GSM externe pour améliorer la qualité du signal. Mettre à jour le firmware du système central.',
            ],
            [
                'titre' => 'Entretien Bassin Rétention B3',
                'date_intervention' => Carbon::now()->subDays(10),
                'lieu' => 'Bassin de Rétention B3',
                'type_intervention' => 'Entretien Préventif',
                'actions' => 'Curage partiel du bassin, nettoyage des déversoirs, débroussaillage des abords, vérification de l\'étanchéité.',
                'resultats' => 'Capacité de stockage restaurée à 95%. Évacuateurs fonctionnels. Végétation contrôlée.',
                'problemes' => 'Accumulation importante de sédiments (environ 200 m³). Quelques fissures superficielles sur les parois.',
                'recommandations' => 'Programmer un curage complet avant la saison des hautes eaux. Surveiller l\'évolution des fissures et prévoir des réparations si nécessaire.',
            ],
        ];

        foreach ($rapports as $rapport) {
            Rapport::create([
                'titre' => $rapport['titre'],
                'date_intervention' => $rapport['date_intervention'],
                'lieu' => $rapport['lieu'],
                'type_intervention' => $rapport['type_intervention'],
                'actions' => $rapport['actions'],
                'resultats' => $rapport['resultats'],
                'problemes' => $rapport['problemes'],
                'recommandations' => $rapport['recommandations'],
                'id_utilisateur' => $utilisateurs->random(),
                'id_tache' => rand(0, 1) ? $taches->random() : null,
                'id_evenement' => rand(0, 1) ? $evenements->random() : null,
            ]);
        }
    }
}
