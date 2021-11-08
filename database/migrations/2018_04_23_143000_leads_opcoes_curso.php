<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LeadsOpcoesCurso extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //

        Schema::table('leads', function (Blueprint $table) {
            $table->integer('opcao_curso_1')->unsigned()->nullable();
            $table->integer('opcao_curso_2')->unsigned()->nullable();
            $table->integer('opcao_curso_3')->unsigned()->nullable();

            $table->foreign('opcao_curso_1')->references('id')->on('cursos');
            $table->foreign('opcao_curso_2')->references('id')->on('cursos');
            $table->foreign('opcao_curso_3')->references('id')->on('cursos');
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
