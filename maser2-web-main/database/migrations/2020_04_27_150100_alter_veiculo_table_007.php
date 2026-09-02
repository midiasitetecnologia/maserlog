<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterVeiculoTable007 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        if (Schema::hasTable('veiculo')) {

            if (Schema::hasColumn('veiculo', 'descricao')) {

                Schema::table('veiculo', function (Blueprint $table) {
                    $table->dropColumn('descricao');
                });
            }

            Schema::table('veiculo', function (Blueprint $table) {
                $table->string('nivel_cons', 1)->nullable()->after('cap_kg');                
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
                $table->string('descricao', 60)->nullable()->after('placa');                
            });
        }
        
        if (Schema::hasColumn('veiculo', 'nivel_cons')) {

            Schema::table('veiculo', function (Blueprint $table) {
                $table->dropColumn('nivel_cons');
            });
        }
    }
}
