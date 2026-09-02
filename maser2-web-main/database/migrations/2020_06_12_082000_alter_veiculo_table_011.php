<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterVeiculoTable011 extends Migration
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
                $table->time('dur_atend_atual')->nullable()->after('dt_transfer_code');
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

            if (Schema::hasColumn('veiculo', 'dur_atend_atual')) {

                Schema::table('veiculo', function (Blueprint $table) {
                    $table->dropColumn('dur_atend_atual');
                });
            }
        }
    }
}
