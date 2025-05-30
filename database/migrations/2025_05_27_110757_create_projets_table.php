<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projets', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 100);
            $table->text('description');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->string('zone_geographique', 100);
            $table->foreignId('id_responsable')->constrained('users')->onDelete('restrict');
            $table->enum('statut', ['planifie', 'en_cours', 'termine', 'suspendu'])->default('planifie');
            $table->timestamps();

            // Indexes
            $table->index('statut');
            $table->index('date_debut');
            $table->index('date_fin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projets');
    }
};
