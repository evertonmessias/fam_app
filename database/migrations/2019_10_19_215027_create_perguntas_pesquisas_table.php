<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePerguntasPesquisasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('perguntas_pesquisas', function (Blueprint $table) {
            $table->increments('id');
            $table->text('pergunta');
            $table->text('respostas')->nullable();
            $table->boolean('multipla_escolha');
            $table->smallInteger('ordem');
            $table->string('identifier');
            $table->timestamps();
            
            $table->index('identifier');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('perguntas_pesquisas');
    }
}
