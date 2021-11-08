<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Datalists extends Migration
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
        // E-mail diário
        Schema::create('datalists', function (Blueprint $table) {
            $table->increments('id');
            $table->string('list');
            $table->string('key')->nullable();
            $table->text('value')->nullable();
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
