<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CampanhasUnidades extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //

        Schema::create('campanha_unidades', function (Blueprint $table) {
            $table->integer('campanha_id')->unsigned();
            $table->integer('unidade_id')->unsigned();

            $table->foreign('campanha_id')->references('id')->on('campanhas');
            $table->foreign('unidade_id')->references('id')->on('unidades');

            $table->unique(['campanha_id', 'unidade_id']);
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

        Schema::drop('campanha_midia');
    }
}
