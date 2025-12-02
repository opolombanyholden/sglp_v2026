<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->enum('priorite_niveau', ['normale', 'moyenne', 'haute', 'urgente'])
                  ->default('normale')
                  ->after('statut');
            $table->boolean('priorite_urgente')
                  ->default(false)
                  ->after('priorite_niveau');
            $table->integer('ordre_traitement')
                  ->nullable()
                  ->after('priorite_urgente');
        });
    }

    public function down(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropColumn(['priorite_niveau', 'priorite_urgente', 'ordre_traitement']);
        });
    }
};