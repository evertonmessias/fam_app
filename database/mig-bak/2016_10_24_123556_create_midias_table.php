<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMidiasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('midias', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome');
            $table->integer('fornecedor')->unsigned();
            $table->integer('midia')->unsigned();

            $table->foreign('fornecedor')->references('id')->on('fornecedores');
            $table->foreign('midia')->references('id')->on('midias_tipos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('midias');
    }
}
