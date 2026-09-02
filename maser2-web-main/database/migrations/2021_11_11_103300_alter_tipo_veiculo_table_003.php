<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTipoVeiculoTable003 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tipo_veiculo')) {

            Schema::table('tipo_veiculo', function (Blueprint $table) {
                $table->time('tempo_desloc_pavilhao')->nullable()->after('dur_prev_atend');
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
        if (Schema::hasTable('tipo_veiculo')) {

            if (Schema::hasColumn('tipo_veiculo', 'tempo_desloc_pavilhao')) {

                Schema::table('tipo_veiculo', function (Blueprint $table) {
                    $table->dropColumn('tempo_desloc_pavilhao');
                });
            }
        }
    }
}
