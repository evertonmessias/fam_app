<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TopOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //

        Schema::table('top_perguntas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order')->default(0);
        });

        Schema::table('top_resultados', function (Blueprint $table) {
            $table->increments('id');
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

        Schema::drop('cursos_categorias');
        Schema::drop('top_perguntas');
        Schema::drop('top_resultados');
    }
}
