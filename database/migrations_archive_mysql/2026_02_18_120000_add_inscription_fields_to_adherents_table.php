<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adherents', function (Blueprint $table) {
            $table->foreignId('inscription_link_id')
                ->nullable()
                ->after('fondateur_id')
                ->constrained('inscription_links')
                ->nullOnDelete();

            $table->string('source_inscription', 30)
                ->default('operateur')
                ->after('inscription_link_id');

            $table->string('statut_inscription', 30)
                ->nullable()
                ->after('source_inscription');

            $table->foreignId('validee_par')
                ->nullable()
                ->after('statut_inscription')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('validee_le')
                ->nullable()
                ->after('validee_par');

            $table->text('motif_rejet_inscription')
                ->nullable()
                ->after('validee_le');

            // Index pour les requêtes fréquentes
            $table->index(['source_inscription', 'statut_inscription'], 'idx_adherents_inscription_status');
            $table->index('inscription_link_id', 'idx_adherents_inscription_link');
        });
    }

    public function down(): void
    {
        Schema::table('adherents', function (Blueprint $table) {
            $table->dropIndex('idx_adherents_inscription_status');
            $table->dropIndex('idx_adherents_inscription_link');
            $table->dropForeign(['inscription_link_id']);
            $table->dropForeign(['validee_par']);
            $table->dropColumn([
                'inscription_link_id',
                'source_inscription',
                'statut_inscription',
                'validee_par',
                'validee_le',
                'motif_rejet_inscription',
            ]);
        });
    }
};
