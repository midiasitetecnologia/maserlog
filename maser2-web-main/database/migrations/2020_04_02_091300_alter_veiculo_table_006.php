<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterVeiculoTable006 extends Migration
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
                $table->timestamp('dt_carga_ocup')->nullable()->after('ocup_veiculo');
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

            if (Schema::hasColumn('veiculo', 'dt_carga_ocup')) {

                Schema::table('veiculo', function (Blueprint $table) {
                    $table->dropColumn('dt_carga_ocup');
                });
            }
        }
    }
}
