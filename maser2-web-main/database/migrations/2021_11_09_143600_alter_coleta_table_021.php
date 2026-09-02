<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaTable021 extends Migration
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
                $table->time('hr_partida_coleta')->nullable()->after('dt_efet_coleta');
                $table->time('hr_partida_entrega')->nullable()->after('dt_efet_entrega');
                $table->time('tempo_desloc_pavilhao')->nullable()->after('hr_sai_entrega');
                $table->string('entrega_consolidada', 1)->nullable()->after('tempo_desloc_pavilhao');
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

            if (Schema::hasColumn('coleta', 'hr_partida_coleta')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('hr_partida_coleta');
                });
            }

            if (Schema::hasColumn('coleta', 'hr_partida_entrega')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('hr_partida_entrega');
                });
            }

            if (Schema::hasColumn('coleta', 'tempo_desloc_pavilhao')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('tempo_desloc_pavilhao');
                });
            }

            if (Schema::hasColumn('coleta', 'entrega_consolidada')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('entrega_consolidada');
                });
            }
        }
    }
}
