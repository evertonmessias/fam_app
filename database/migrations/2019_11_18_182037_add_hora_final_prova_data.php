<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHoraFinalProvaData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('provas_datas', function (Blueprint $table) {
            $table->datetime('hora_final')->nullable()->after('hora');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('provas_datas', function (Blueprint $table) {
            $table->dropColumn('hora_final');
        });
    }
}
