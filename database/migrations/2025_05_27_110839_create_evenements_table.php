<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evenements', function (Blueprint $table) {
            $table->id();
            $table->string('titre', 100);
            $table->text('description');
            $table->enum('type', ['intervention', 'reunion', 'formation', 'visite']);
            $table->dateTime('date_debut');
            $table->dateTime('date_fin');
            $table->string('lieu', 100);
            $table->string('coordonnees_gps', 50)->nullable();
            $table->enum('statut', ['planifie', 'en_cours', 'termine', 'annule', 'reporte'])->default('planifie');
            $table->enum('priorite', ['normale', 'haute', 'urgente'])->default('normale');
            $table->foreignId('id_organisateur')->constrained('users')->onDelete('restrict');
            $table->foreignId('id_projet')->nullable()->constrained('projets')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->index('date_debut');
            $table->index('statut');
            $table->index('type');
            $table->index('priorite');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evenements');
    }
};
