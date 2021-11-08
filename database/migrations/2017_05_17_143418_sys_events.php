<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SysEvents extends Migration
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
        // Eventos
        Schema::create('sys_events', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('time')->useCurrent();
            $table->string('type');
            $table->string('message');
            $table->boolean('finished')->unsigned()->default(false);
            $table->text('meta')->nullable();
            $table->text('error')->nullable();
            $table->integer('process_id')->unsigned()->nullable();
            $table->integer('user_id')->unsigned()->nullable();

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
    }
}
