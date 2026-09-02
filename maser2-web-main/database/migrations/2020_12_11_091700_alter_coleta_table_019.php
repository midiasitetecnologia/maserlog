<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaTable019 extends Migration
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
                $table->string('carga_pavilhao', 1)->nullable()->after('status');
            });

            Schema::table('coleta', function (Blueprint $table) {
                $table->string('mot_nao_entrega', 2)->nullable()->after('obs_nao_coleta');
            });

            Schema::table('coleta', function (Blueprint $table) {
                $table->string('obs_nao_entrega')->nullable()->after('mot_nao_entrega');
            });

            Schema::table('coleta', function (Blueprint $table) {
                $table->string('reentrega', 1)->nullable()->after('obs_nao_entrega');
            });

            Schema::table('coleta', function (Blueprint $table) {
                $table->string('reentrega_gerada', 1)->nullable()->after('reentrega');
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

            if (Schema::hasColumn('coleta', 'carga_pavilhao')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('carga_pavilhao');
                });
            }

            if (Schema::hasColumn('coleta', 'mot_nao_entrega')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('mot_nao_entrega');
                });
            }

            if (Schema::hasColumn('coleta', 'obs_nao_entrega')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('obs_nao_entrega');
                });
            }

            if (Schema::hasColumn('coleta', 'reentrega')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('reentrega');
                });
            }

            if (Schema::hasColumn('coleta', 'reentrega_gerada')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('reentrega_gerada');
                });
            }
        }
    }
}
