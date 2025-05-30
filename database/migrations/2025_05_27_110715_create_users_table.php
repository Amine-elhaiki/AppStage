<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 50);
            $table->string('prenom', 50);
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'technicien']);
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->string('telephone', 20)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            // Indexes
            $table->index('email');
            $table->index(['role', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
