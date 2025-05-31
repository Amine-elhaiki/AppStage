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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('description');
            $table->enum('statut', ['planifie', 'en_cours', 'suspendu', 'termine', 'annule'])->default('planifie');
            $table->enum('priorite', ['basse', 'normale', 'haute', 'urgente'])->default('normale');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->decimal('budget', 12, 2)->nullable();
            $table->string('zone_geographique');
            $table->integer('pourcentage_avancement')->default(0);
            $table->foreignId('responsable_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
