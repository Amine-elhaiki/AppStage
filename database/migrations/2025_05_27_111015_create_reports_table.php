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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description');
            $table->enum('type', ['intervention', 'maintenance', 'inspection', 'reparation', 'installation', 'autre'])->default('intervention');
            $table->datetime('date_intervention');
            $table->string('lieu');
            $table->text('probleme_identifie')->nullable();
            $table->text('actions_effectuees');
            $table->text('materiels_utilises')->nullable();
            $table->enum('etat_equipement', ['bon', 'moyen', 'mauvais', 'hors_service'])->nullable();
            $table->text('recommandations')->nullable();
            $table->decimal('cout_intervention', 10, 2)->nullable();
            $table->enum('statut', ['brouillon', 'soumis', 'valide', 'rejete'])->default('brouillon');
            $table->json('photos')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('event_id')->nullable()->constrained('events');
            $table->foreignId('project_id')->nullable()->constrained('projects');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
