<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Extension `unaccent` : les recherches `like` ignorent les accents, comme sous
 * MySQL (« Eglise » trouve « Église »). Voir App\Database\PostgresInsensibleCasseGrammar.
 *
 * Extension « trusted » depuis PostgreSQL 13 : le propriétaire de la base peut
 * l'installer sans être superutilisateur.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS unaccent');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP EXTENSION IF EXISTS unaccent');
        }
    }
};
