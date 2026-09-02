<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaNfTable005 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('coleta_nf')) {

            if (Schema::hasColumn('coleta_nf', 'solic_distrib_id')) {

                Schema::table('coleta_nf', function (Blueprint $table) {
                    $table->renameColumn('solic_distrib_id', 'solic_destino_id');
                });
            }

            Schema::table('coleta_nf', function (Blueprint $table) {
                $table->string('mot_nao_entrega', 2)->nullable()->after('solic_destino_id');
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
        if (Schema::hasTable('coleta_nf')) {

            if (Schema::hasColumn('coleta_nf', 'solic_destino_id')) {

                Schema::table('coleta_nf', function (Blueprint $table) {
                    $table->renameColumn('solic_destino_id', 'solic_distrib_id');
                });
            }

            if (Schema::hasColumn('coleta_nf', 'mot_nao_entrega')) {

                Schema::table('coleta_nf', function (Blueprint $table) {
                    $table->dropColumn('mot_nao_entrega');
                });
            }
        }
    }
}
