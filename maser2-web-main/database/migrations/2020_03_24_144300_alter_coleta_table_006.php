<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaTable006 extends Migration
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
                $table->string('local_coleta_cmd', 50)->nullable()->after('cod_loc_coleta');
            });

            Schema::table('coleta', function (Blueprint $table) {
                $table->string('local_entrega_cmd', 50)->nullable()->after('cod_loc_entrega');
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

            if (Schema::hasColumn('coleta', 'local_coleta_cmd')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('local_coleta_cmd');
                });
            }

            if (Schema::hasColumn('coleta', 'local_entrega_cmd')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('local_entrega_cmd');
                });
            }
        }       
    }
}
