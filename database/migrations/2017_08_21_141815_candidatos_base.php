<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CandidatosBase extends Migration
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
        // Candidatos Base
        Schema::create('candidatos_base', function (Blueprint $table) {
            $table->string('cpf');
            $table->integer('base')->unsigned();
            $table->timestamp('dataalteracao')->default(\DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->integer('lead_id')->unsigned();
            $table->string('curso')->nullable();

            $table->foreign('lead_id')->references('id')->on('leads');
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

        Schema::drop('candidatos_base');
    }
}
