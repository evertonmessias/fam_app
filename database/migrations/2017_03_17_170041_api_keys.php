<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ApiKeys extends Migration
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
        // API Keys
        Schema::create('api_keys', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key')->unique();
            $table->string('private_key')->unique();
            $table->string('description')->nullable();
            $table->softDeletes();
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
