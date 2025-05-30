<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pieces_jointes', function (Blueprint $table) {
            $table->id();
            $table->string('nom_fichier', 255);
            $table->string('type_fichier', 50);
            $table->integer('taille')->comment('Size in bytes');
            $table->string('chemin', 255);
            $table->foreignId('id_rapport')->constrained('rapports')->onDelete('cascade');
            $table->timestamps();

            // Indexes
            $table->index('type_fichier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pieces_jointes');
    }
};
