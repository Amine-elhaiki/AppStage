<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Administrateur par défaut
        User::create([
            'nom' => 'Admin',
            'prenom' => 'Système',
            'email' => 'admin@ormvat.ma',
            'telephone' => '+212 523 123456',
            'role' => 'admin',
            'statut' => 'actif',
            'password' => Hash::make('admin123'),
            'dernier_connexion' => now(),
        ]);

        // Directeur technique
        User::create([
            'nom' => 'Bennani',
            'prenom' => 'Mohammed',
            'email' => 'mbennani@ormvat.ma',
            'telephone' => '+212 661 234567',
            'role' => 'admin',
            'statut' => 'actif',
            'password' => Hash::make('password123'),
            'dernier_connexion' => now()->subHours(2),
        ]);

        // Techniciens de terrain
        $technicians = [
            [
                'nom' => 'Alami',
                'prenom' => 'Ahmed',
                'email' => 'aalami@ormvat.ma',
                'telephone' => '+212 662 345678',
                'specialite' => 'Irrigation'
            ],
            [
                'nom' => 'Tazi',
                'prenom' => 'Fatima',
                'email' => 'ftazi@ormvat.ma',
                'telephone' => '+212 663 456789',
                'specialite' => 'Maintenance'
            ],
            [
                'nom' => 'Idrissi',
                'prenom' => 'Omar',
                'email' => 'oidrissi@ormvat.ma',
                'telephone' => '+212 664 567890',
                'specialite' => 'Hydraulique'
            ],
            [
                'nom' => 'Benjelloun',
                'prenom' => 'Aicha',
                'email' => 'abenjelloun@ormvat.ma',
                'telephone' => '+212 665 678901',
                'specialite' => 'Contrôle qualité'
            ],
            [
                'nom' => 'Fassi',
                'prenom' => 'Youssef',
                'email' => 'yfassi@ormvat.ma',
                'telephone' => '+212 666 789012',
                'specialite' => 'Électromécanique'
            ],
            [
                'nom' => 'Berrada',
                'prenom' => 'Zineb',
                'email' => 'zberrada@ormvat.ma',
                'telephone' => '+212 667 890123',
                'specialite' => 'Instrumentation'
            ],
            [
                'nom' => 'Lahlou',
                'prenom' => 'Karim',
                'email' => 'klahlou@ormvat.ma',
                'telephone' => '+212 668 901234',
                'specialite' => 'Génie civil'
            ],
            [
                'nom' => 'Mansouri',
                'prenom' => 'Nadia',
                'email' => 'nmansouri@ormvat.ma',
                'telephone' => '+212 669 012345',
                'specialite' => 'Topographie'
            ]
        ];

        foreach ($technicians as $tech) {
            User::create([
                'nom' => $tech['nom'],
                'prenom' => $tech['prenom'],
                'email' => $tech['email'],
                'telephone' => $tech['telephone'],
                'role' => 'technicien',
                'statut' => 'actif',
                'password' => Hash::make('password123'),
                'dernier_connexion' => now()->subHours(rand(1, 48)),
            ]);
        }

        // Technicien test (pour les démonstrations)
        User::create([
            'nom' => 'Test',
            'prenom' => 'Technicien',
            'email' => 'technicien@ormvat.ma',
            'telephone' => '+212 660 123456',
            'role' => 'technicien',
            'statut' => 'actif',
            'password' => Hash::make('tech123'),
            'dernier_connexion' => now()->subMinutes(30),
        ]);

        // Quelques utilisateurs inactifs pour les tests
        User::create([
            'nom' => 'Ancien',
            'prenom' => 'Employé',
            'email' => 'ancien@ormvat.ma',
            'telephone' => '+212 670 123456',
            'role' => 'technicien',
            'statut' => 'inactif',
            'password' => Hash::make('password123'),
            'dernier_connexion' => now()->subMonths(6),
        ]);

        $this->command->info('Utilisateurs créés avec succès!');
        $this->command->info('Connexions de test:');
        $this->command->info('Admin: admin@ormvat.ma / admin123');
        $this->command->info('Technicien: technicien@ormvat.ma / tech123');
    }
}
