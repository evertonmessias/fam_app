<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Top extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //

        Schema::create('cursos_categorias', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('nome');
            $table->text('descricao')->nullable();
        });

        Schema::table('cursos', function (Blueprint $table) {
            $table->string('landing_page')->nullable();
            $table->string('categoria_id')->nullable();

            $table->foreign('categoria_id')->references('id')->on('cursos_categorias');
        });

        Schema::create('top_perguntas', function (Blueprint $table) {
            $table->string('pergunta');
            $table->text('respostas');
        });

        Schema::create('top_resultados', function (Blueprint $table) {
            $table->string('email');
            $table->integer('aluno_id')->unsigned()->nullable();
            $table->text('resultados');
            $table->text('raw');
            $table->timestamps();

            $table->foreign('aluno_id')->references('id')->on('alunos');
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
