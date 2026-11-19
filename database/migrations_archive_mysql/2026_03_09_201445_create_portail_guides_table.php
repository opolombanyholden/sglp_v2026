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
        Schema::create('portail_guides', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->string('categorie')->default('Général');
            $table->string('chemin_fichier')->nullable();
            $table->string('url_externe')->nullable();
            $table->unsignedInteger('nombre_pages')->default(0);
            $table->unsignedBigInteger('nombre_telechargements')->default(0);
            $table->boolean('est_actif')->default(true);
            $table->unsignedInteger('ordre')->default(0);
            $table->timestamps();
            $table->index(['est_actif', 'ordre']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('portail_guides');
    }
};
