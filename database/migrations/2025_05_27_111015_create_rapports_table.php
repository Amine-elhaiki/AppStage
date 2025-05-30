<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rapports', function (Blueprint $table) {
            $table->id();
            $table->string('titre', 100);
            $table->date('date_intervention');
            $table->string('lieu', 100);
            $table->string('type_intervention', 50);
            $table->text('actions');
            $table->text('resultats');
            $table->text('problemes')->nullable();
            $table->text('recommandations')->nullable();
            $table->foreignId('id_utilisateur')->constrained('users')->onDelete('restrict');
            $table->foreignId('id_tache')->nullable()->constrained('taches')->onDelete('set null');
            $table->foreignId('id_evenement')->nullable()->constrained('evenements')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->index('date_intervention');
            $table->index('type_intervention');
            $table->index('id_utilisateur');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rapports');
    }
};
