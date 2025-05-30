<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\Evenement;
use App\Models\User;
use App\Models\Projet;
use Carbon\Carbon;

class EvenementSeeder extends Seeder
{
    public function run(): void
    {
        $organisateurs = User::all()->pluck('id');
        $projets = Projet::all()->pluck('id');

        $evenements = [
            // Interventions techniques
            [
                'titre' => 'Inspection Canal Principal B4',
                'description' => 'Inspection technique complète du canal principal du secteur B4 pour évaluer l\'état des infrastructures.',
                'type' => 'intervention',
                'date_debut' => Carbon::tomorrow()->setTime(8, 0),
                'date_fin' => Carbon::tomorrow()->setTime(12, 0),
                'lieu' => 'Canal Principal Secteur B4',
                'priorite' => 'haute',
                'statut' => 'planifie',
            ],
            [
                'titre' => 'Maintenance Station de Pompage P12',
                'description' => 'Maintenance préventive programmée de la station de pompage P12.',
                'type' => 'intervention',
                'date_debut' => Carbon::now()->addDays(3)->setTime(7, 30),
                'date_fin' => Carbon::now()->addDays(3)->setTime(16, 30),
                'lieu' => 'Station de Pompage P12',
                'priorite' => 'normale',
                'statut' => 'planifie',
            ],
            [
                'titre' => 'Réparation Urgente Vanne Distribution',
                'description' => 'Intervention d\'urgence pour réparer la vanne de distribution défaillante du secteur Fkih Ben Salah.',
                'type' => 'intervention',
                'date_debut' => Carbon::now()->addHours(2),
                'date_fin' => Carbon::now()->addHours(6),
                'lieu' => 'Secteur Fkih Ben Salah - Vanne V15',
                'priorite' => 'urgente',
                'statut' => 'planifie',
            ],

            // Réunions
            [
                'titre' => 'Réunion Hebdomadaire Équipe Technique',
                'description' => 'Point hebdomadaire sur l\'avancement des projets et coordination des interventions.',
                'type' => 'reunion',
                'date_debut' => Carbon::now()->next('Monday')->setTime(9, 0),
                'date_fin' => Carbon::now()->next('Monday')->setTime(10, 30),
                'lieu' => 'Salle de Réunion - Siège ORMVAT',
                'priorite' => 'normale',
                'statut' => 'planifie',
            ],
            [
                'titre' => 'Comité de Pilotage Projet Modernisation',
                'description' => 'Réunion du comité de pilotage pour faire le point sur le projet de modernisation du secteur Tadla Nord.',
                'type' => 'reunion',
                'date_debut' => Carbon::now()->addWeek()->setTime(14, 0),
                'date_fin' => Carbon::now()->addWeek()->setTime(16, 0),
                'lieu' => 'Salle de Conférence - Siège ORMVAT',
                'priorite' => 'haute',
                'statut' => 'planifie',
            ],

            // Formations
            [
                'titre' => 'Formation Sécurité Interventions Hydrauliques',
                'description' => 'Session de formation sur les procédures de sécurité lors des interventions sur les infrastructures hydrauliques.',
                'type' => 'formation',
                'date_debut' => Carbon::now()->addDays(10)->setTime(9, 0),
                'date_fin' => Carbon::now()->addDays(10)->setTime(17, 0),
                'lieu' => 'Centre de Formation ORMVAT',
                'priorite' => 'normale',
                'statut' => 'planifie',
            ],
            [
                'titre' => 'Atelier Nouvelles Technologies Irrigation',
                'description' => 'Formation pratique sur les nouvelles technologies d\'irrigation et de gestion de l\'eau.',
                'type' => 'formation',
                'date_debut' => Carbon::now()->addDays(15)->setTime(8, 30),
                'date_fin' => Carbon::now()->addDays(15)->setTime(12, 30),
                'lieu' => 'Site Expérimental Tadla',
                'priorite' => 'normale',
                'statut' => 'planifie',
            ],

            // Visites
            [
                'titre' => 'Visite Délégation Ministère Agriculture',
                'description' => 'Accueil et accompagnement d\'une délégation du Ministère de l\'Agriculture pour présenter les projets en cours.',
                'type' => 'visite',
                'date_debut' => Carbon::now()->addDays(7)->setTime(10, 0),
                'date_fin' => Carbon::now()->addDays(7)->setTime(15, 0),
                'lieu' => 'Siège ORMVAT et Sites de Projet',
                'priorite' => 'haute',
                'statut' => 'planifie',
            ],
        ];

        foreach ($evenements as $evenement) {
            $event = Evenement::create([
                'titre' => $evenement['titre'],
                'description' => $evenement['description'],
                'type' => $evenement['type'],
                'date_debut' => $evenement['date_debut'],
                'date_fin' => $evenement['date_fin'],
                'lieu' => $evenement['lieu'],
                'priorite' => $evenement['priorite'],
                'statut' => $evenement['statut'],
                'id_organisateur' => $organisateurs->random(),
                'id_projet' => $projets->random(),
            ]);

            // Ajouter des participants aléatoires
            $participants = $organisateurs->random(rand(2, 5));
            foreach ($participants as $participantId) {
                $event->participants()->attach($participantId, [
                    'statut_presence' => collect(['invite', 'confirme', 'decline'])->random()
                ]);
            }
        }
    }
}
