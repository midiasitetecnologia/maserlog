<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterVeiculoTable009 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('veiculo')) {

            Schema::table('veiculo', function (Blueprint $table) {
                $table->string('placa_cavalo', 8)->nullable()->after('usar_gps');
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
        if (Schema::hasTable('veiculo')) {

            if (Schema::hasColumn('veiculo', 'placa_cavalo')) {

                Schema::table('veiculo', function (Blueprint $table) {
                    $table->dropColumn('placa_cavalo');
                });
            }
        }
    }
}
