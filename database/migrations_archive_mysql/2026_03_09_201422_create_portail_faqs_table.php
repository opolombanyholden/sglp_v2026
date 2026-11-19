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
        Schema::create('portail_faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('reponse');
            $table->string('categorie')->default('Général');
            $table->unsignedInteger('ordre')->default(0);
            $table->boolean('est_actif')->default(true);
            $table->timestamps();
            $table->index(['est_actif', 'categorie', 'ordre']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('portail_faqs');
    }
};
