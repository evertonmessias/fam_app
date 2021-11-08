<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Crm\Atendimento;

class CrmAtendimentos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //

        ////////////////////////////////////////////
        // CRM :: Atendimentos
        Schema::create('crm_atendimentos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('aluno_id')->unsigned();
            $table->integer('lead_id')->unsigned();
            $table->integer('user_id')->unsigned()->nullable();
            $table->datetime('agendamento')->nullable();

            $table->foreign('aluno_id')->references('id')->on('alunos');
            $table->foreign('lead_id')->references('id')->on('leads');
            $table->foreign('user_id')->references('id')->on('users');
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

        Schema::drop('crm_atendimentos');
    }
}
