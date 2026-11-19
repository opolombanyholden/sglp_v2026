<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fonctions', function (Blueprint $table) {
            if (!Schema::hasColumn('fonctions', 'suggested_by_user_id')) {
                $table->unsignedBigInteger('suggested_by_user_id')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('fonctions', 'suggestion_status')) {
                $table->enum('suggestion_status', ['approved', 'pending', 'rejected'])->default('approved')->after('suggested_by_user_id');
            }
        });

        Schema::table('domaines_activite', function (Blueprint $table) {
            if (!Schema::hasColumn('domaines_activite', 'suggested_by_user_id')) {
                $table->unsignedBigInteger('suggested_by_user_id')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('domaines_activite', 'suggestion_status')) {
                $table->enum('suggestion_status', ['approved', 'pending', 'rejected'])->default('approved')->after('suggested_by_user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fonctions', function (Blueprint $table) {
            if (Schema::hasColumn('fonctions', 'suggested_by_user_id')) $table->dropColumn('suggested_by_user_id');
            if (Schema::hasColumn('fonctions', 'suggestion_status')) $table->dropColumn('suggestion_status');
        });
        Schema::table('domaines_activite', function (Blueprint $table) {
            if (Schema::hasColumn('domaines_activite', 'suggested_by_user_id')) $table->dropColumn('suggested_by_user_id');
            if (Schema::hasColumn('domaines_activite', 'suggestion_status')) $table->dropColumn('suggestion_status');
        });
    }
};
