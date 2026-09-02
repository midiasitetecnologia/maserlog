<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterVeiculoTable003 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('veiculo')) {                     

            if (Schema::hasColumn('veiculo', 'km_atual')) {

                Schema::table('veiculo', function (Blueprint $table) {
                    $table->dropColumn('km_atual');
                });

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

            Schema::table('veiculo', function (Blueprint $table) {
                $table->unsignedInteger('km_atual')->nullable()->after('cap_kg');
            });              
            
        }
    }
}
