<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterVeiculoTable012 extends Migration
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
                $table->string('usar_gps', 1)->default('V')->nullable()->change();
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

            Schema::table('veiculo', function (Blueprint $table) {
                $table->string('usar_gps', 1)->default('M')->nullable()->change();
            });
        }
    }
}
