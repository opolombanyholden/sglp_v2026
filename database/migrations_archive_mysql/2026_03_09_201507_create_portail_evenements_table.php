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
        Schema::create('portail_evenements', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->enum('type', ['echeance', 'formation', 'maintenance', 'evenement'])->default('evenement');
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->string('lieu')->nullable();
            $table->string('url')->nullable();
            $table->boolean('est_important')->default(false);
            $table->boolean('est_actif')->default(true);
            $table->timestamps();
            $table->index(['est_actif', 'date_debut']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('portail_evenements');
    }
};
