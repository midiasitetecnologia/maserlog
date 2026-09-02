<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterVeiculoTable010 extends Migration
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
                $table->smallInteger('transfer_code')->nullable()->after('dt_carga_ocup');
                $table->timestamp('dt_transfer_code')->nullable()->after('transfer_code');
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

            if (Schema::hasColumn('veiculo', 'transfer_code')) {

                Schema::table('veiculo', function (Blueprint $table) {
                    $table->dropColumn('transfer_code');
                });
            }

            if (Schema::hasColumn('veiculo', 'dt_transfer_code')) {

                Schema::table('veiculo', function (Blueprint $table) {
                    $table->dropColumn('dt_transfer_code');
                });
            }
        }
    }
}
