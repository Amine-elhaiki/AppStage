<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taches', function (Blueprint $table) {
            $table->id();
            $table->string('titre', 100);
            $table->text('description');
            $table->date('date_echeance');
            $table->enum('priorite', ['basse', 'moyenne', 'haute'])->default('moyenne');
            $table->enum('statut', ['a_faire', 'en_cours', 'termine'])->default('a_faire');
            $table->integer('progression')->default(0)->comment('Pourcentage de 0 à 100');
            $table->foreignId('id_utilisateur')->constrained('users')->onDelete('restrict');
            $table->foreignId('id_projet')->nullable()->constrained('projets')->onDelete('set null');
            $table->foreignId('id_evenement')->nullable()->constrained('evenements')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->index('date_echeance');
            $table->index('statut');
            $table->index('priorite');
            $table->index(['id_utilisateur', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taches');
    }
};
