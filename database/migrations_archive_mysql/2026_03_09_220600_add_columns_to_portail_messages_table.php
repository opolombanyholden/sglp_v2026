<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('portail_messages', function (Blueprint $table) {
            $table->string('nom')->after('id');
            $table->string('email')->after('nom');
            $table->string('sujet')->after('email');
            $table->text('message')->after('sujet');
            $table->enum('statut', ['non_lu', 'lu', 'traite', 'archive'])->default('non_lu')->after('message');
            $table->text('reponse')->nullable()->after('statut');
            $table->timestamp('date_reponse')->nullable()->after('reponse');
            $table->string('ip_address', 45)->nullable()->after('date_reponse');
            $table->string('user_agent')->nullable()->after('ip_address');
            $table->index(['statut', 'created_at']);
        });
    }

    public function down()
    {
        Schema::table('portail_messages', function (Blueprint $table) {
            $table->dropColumn(['nom', 'email', 'sujet', 'message', 'statut', 'reponse', 'date_reponse', 'ip_address', 'user_agent']);
        });
    }
};
