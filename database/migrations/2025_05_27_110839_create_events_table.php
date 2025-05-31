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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description');
            $table->enum('type', ['intervention', 'reunion', 'formation', 'visite', 'maintenance', 'autre'])->default('intervention');
            $table->enum('statut', ['planifie', 'en_cours', 'termine', 'reporte', 'annule'])->default('planifie');
            $table->datetime('date_debut');
            $table->datetime('date_fin');
            $table->string('lieu');
            $table->text('participants')->nullable();
            $table->text('materiels_requis')->nullable();
            $table->text('resultats')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('project_id')->nullable()->constrained('projects');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
