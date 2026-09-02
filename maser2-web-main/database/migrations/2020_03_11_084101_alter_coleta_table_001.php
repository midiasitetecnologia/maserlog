<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaTable001 extends Migration
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
                $table->renameColumn('tipo_carga', 'sis_carga');
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
                $table->renameColumn('sis_carga', 'tipo_carga');
            });
            
        }
    }
}
