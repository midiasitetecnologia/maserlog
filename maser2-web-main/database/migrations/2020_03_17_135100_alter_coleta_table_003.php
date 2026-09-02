<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaTable003 extends Migration
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
                $table->timestamp('dt_desloca_coleta')->nullable()->after('receber_nf_frete');
            });

            Schema::table('coleta', function (Blueprint $table) {
                $table->timestamp('dt_desloca_entrega')->nullable()->after('km_final');
            });

            Schema::table('coleta', function (Blueprint $table) {
                $table->unsignedInteger('km_ini_entrega')->nullable()->after('dt_desloca_entrega');
            });

            Schema::table('coleta', function (Blueprint $table) {
                $table->unsignedInteger('km_fim_entrega')->nullable()->after('km_ini_entrega');
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

            if (Schema::hasColumn('coleta', 'km_ini_entrega')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('km_ini_entrega');
                });
            }

            if (Schema::hasColumn('coleta', 'km_fim_entrega')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('km_fim_entrega');
                });
            }
        }
    }
}
