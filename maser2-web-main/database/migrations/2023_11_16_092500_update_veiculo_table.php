<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class UpdateVeiculoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('veiculo')) {

            if (Schema::hasColumn('veiculo', 'usar_gps')) {
                DB::statement('UPDATE veiculo SET usar_gps = "N" WHERE usar_gps = "M"');
            }
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

            if (Schema::hasColumn('veiculo', 'usar_gps')) {
                DB::statement('UPDATE veiculo SET usar_gps = "M" WHERE usar_gps = "N"');
            }
        }
    }
}
