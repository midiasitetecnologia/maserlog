<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaTable005 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('coleta')) {

            if (Schema::hasColumn('coleta', 'dt_desloca_coleta')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('dt_desloca_coleta');
                });
            }

            if (Schema::hasColumn('coleta', 'dt_desloca_entrega')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('dt_desloca_entrega');
                });
            }

            if (Schema::hasColumn('coleta', 'descarga_coleta')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('descarga_coleta');
                });
            }

            if (Schema::hasColumn('coleta', 'dt_descarga_coleta')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('dt_descarga_coleta');
                });
            }
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
                $table->timestamp('dt_desloca_coleta')->nullable()->after('receber_nf_frete');
            });

            Schema::table('coleta', function (Blueprint $table) {
                $table->timestamp('dt_desloca_entrega')->nullable()->after('dt_desloca_coleta');
            });

            Schema::table('coleta', function (Blueprint $table) {
                $table->string('descarga_coleta', 1)->nullable()->after('txt_instrucao');
            });

            Schema::table('coleta', function (Blueprint $table) {
                $table->timestamp('dt_descarga_coleta')->nullable()->after('descarga_coleta');
            });
        }        
    }
}
