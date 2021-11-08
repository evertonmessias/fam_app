<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixDatasUpdate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //

        Schema::table('cursos', function ($table) { $table->timestamps(); });
        Schema::table('provas', function ($table) { $table->timestamps(); });
        Schema::table('provas_datas', function ($table) { $table->timestamps(); });
        Schema::table('cidades', function ($table) { $table->timestamps(); });
        Schema::table('estados', function ($table) { $table->timestamps(); });
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
