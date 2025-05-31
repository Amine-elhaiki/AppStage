<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description');
            $table->enum('statut', ['a_faire', 'en_cours', 'termine', 'reporte', 'annule'])->default('a_faire');
            $table->enum('priorite', ['basse', 'normale', 'haute', 'urgente'])->default('normale');
            $table->datetime('date_creation');
            $table->datetime('date_echeance')->nullable();
            $table->datetime('date_debut_reelle')->nullable();
            $table->datetime('date_fin_reelle')->nullable();
            $table->integer('progression')->default(0);
            $table->text('commentaires')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('project_id')->nullable()->constrained('projects');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
