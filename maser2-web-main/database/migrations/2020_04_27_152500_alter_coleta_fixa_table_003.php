<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaFixaTable003 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {        
        if (Schema::hasTable('coleta_fixa')) {

            Schema::table('coleta_fixa', function (Blueprint $table) {
                $table->time('hr_prev_entrega')->nullable()->after('hr_prev_coleta');
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
        if (Schema::hasTable('coleta_fixa')) {

            if (Schema::hasColumn('coleta_fixa', 'hr_prev_entrega')) {

                Schema::table('coleta_fixa', function (Blueprint $table) {
                    $table->dropColumn('hr_prev_entrega');
                });
            }
        }
               
    }
}
