<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CampanhaTags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Tags de Campanha
        Schema::create('campanha_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('desc')->nullable();
            $table->string('pagina')->nullable();
            $table->boolean('topo')->default(false);
            $table->text('codigo')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
