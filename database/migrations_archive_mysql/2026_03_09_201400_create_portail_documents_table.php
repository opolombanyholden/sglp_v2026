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
        Schema::create('portail_documents', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->string('categorie')->default('Guides');
            $table->string('type_organisation')->default('tous');
            $table->string('format')->default('PDF');
            $table->string('taille')->nullable();
            $table->string('chemin_fichier')->nullable();
            $table->string('url_externe')->nullable();
            $table->unsignedBigInteger('nombre_telechargements')->default(0);
            $table->boolean('est_actif')->default(true);
            $table->unsignedInteger('ordre')->default(0);
            $table->timestamps();
            $table->index(['est_actif', 'categorie']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('portail_documents');
    }
};
