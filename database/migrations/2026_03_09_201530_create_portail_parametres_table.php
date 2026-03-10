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
        Schema::create('portail_parametres', function (Blueprint $table) {
            $table->id();
            $table->string('cle')->unique();
            $table->longText('valeur')->nullable();
            $table->enum('type', ['text', 'html', 'json', 'image', 'url', 'email', 'phone'])->default('text');
            $table->string('description')->nullable();
            $table->string('groupe')->default('general');
            $table->timestamps();
            $table->index('groupe');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('portail_parametres');
    }
};
