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
        Schema::create('portail_actualites', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->string('slug')->unique();
            $table->text('extrait')->nullable();
            $table->longText('contenu');
            $table->string('image')->nullable();
            $table->string('categorie')->default('Général');
            $table->string('auteur')->default('Administration');
            $table->enum('statut', ['brouillon', 'publie', 'archive'])->default('brouillon');
            $table->unsignedBigInteger('vues')->default(0);
            $table->boolean('en_une')->default(false);
            $table->timestamp('date_publication')->nullable();
            $table->timestamps();
            $table->index(['statut', 'date_publication']);
            $table->index('categorie');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('portail_actualites');
    }
};
