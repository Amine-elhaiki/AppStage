<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participants_evenements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_evenement')->constrained('evenements')->onDelete('cascade');
            $table->foreignId('id_utilisateur')->constrained('users')->onDelete('cascade');
            $table->enum('statut_presence', ['invite', 'confirme', 'decline', 'present', 'absent'])->default('invite');
            $table->timestamps();

            // Ensure unique participant per event
            $table->unique(['id_evenement', 'id_utilisateur']);

            // Indexes
            $table->index('statut_presence');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participants_evenements');
    }
};
