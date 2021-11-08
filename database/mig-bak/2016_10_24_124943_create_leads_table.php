<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('campanha')->unsigned();
            $table->integer('aluno')->unsigned();
            $table->integer('midia')->unsigned();
            $table->integer('curso')->unsigned();
            $table->integer('prova')->unsigned();
            $table->integer('status')->unsigned();
            $table->timestamps();

            $table->foreign('campanha')->references('id')->on('campanhas');
            $table->foreign('aluno')->references('id')->on('alunos');
            $table->foreign('midia')->references('id')->on('midias');
            $table->foreign('curso')->references('id')->on('cursos');
            $table->foreign('prova')->references('id')->on('provas');
            $table->foreign('status')->references('id')->on('lead_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leads');
    }
}
