<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adherents', function (Blueprint $table) {
            if (!Schema::hasColumn('adherents', 'motif_adhesion')) {
                $table->string('motif_adhesion', 500)->nullable()->after('fonction');
            }
        });
    }

    public function down(): void
    {
        Schema::table('adherents', function (Blueprint $table) {
            if (Schema::hasColumn('adherents', 'motif_adhesion')) {
                $table->dropColumn('motif_adhesion');
            }
        });
    }
};
