<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CampanhasDisparoEmails extends Migration
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
        // E-mails Diários
        Schema::table('campanhas', function (Blueprint $table) {
            $table->boolean('relatorios')->default(false);
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
