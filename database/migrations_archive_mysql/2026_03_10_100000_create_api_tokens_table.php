<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('nom');                          // Nom de l'application cliente
            $table->string('organisation_cliente')->nullable(); // Organisation demandeuse
            $table->string('token', 64)->unique();          // SHA-256 hash du token
            $table->string('prefix', 8);                   // 8 premiers caractères pour identification rapide
            $table->json('permissions')->nullable();        // Scopes autorisés (organisations, stats, verify...)
            $table->integer('rate_limit')->default(60);    // Requêtes par minute
            $table->timestamp('expires_at')->nullable();   // Expiration optionnelle
            $table->timestamp('last_used_at')->nullable();
            $table->string('last_used_ip', 45)->nullable();
            $table->bigInteger('total_requests')->default(0);
            $table->boolean('est_actif')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_tokens');
    }
};
