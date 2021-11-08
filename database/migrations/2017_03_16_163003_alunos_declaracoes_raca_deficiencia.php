<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlunosDeclaracoesRacaDeficiencia extends Migration
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
        // Auto-declaração de Raça/Cor
        Schema::create('autodeclaracao_raca', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigo');
            $table->string('raca');
        });

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Auto-declaração de Deficiência
        Schema::create('autodeclaracao_deficiencia', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigo');
            $table->string('deficiencia');
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
