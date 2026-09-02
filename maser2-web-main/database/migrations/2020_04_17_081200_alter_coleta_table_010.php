<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaTable010 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('coleta')) {

            Schema::table('coleta', function (Blueprint $table) {
                $table->renameColumn('distancia_real', 'distancia_total');
            });

            Schema::table('coleta', function (Blueprint $table) {
                $table->renameColumn('tempo_real', 'tempo_total');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('coleta')) {

            Schema::table('coleta', function (Blueprint $table) {
                $table->renameColumn('distancia_total', 'distancia_real');
            });

            Schema::table('coleta', function (Blueprint $table) {
                $table->renameColumn('tempo_total', 'tempo_real');
            });
        }
    }
}
