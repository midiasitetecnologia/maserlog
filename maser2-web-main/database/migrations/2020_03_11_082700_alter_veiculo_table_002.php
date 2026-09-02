<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterVeiculoTable002 extends Migration
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
                $table->string('sis_carga_empilha', 1)->default('N')->nullable()->after('milk_run');
            });

            Schema::table('veiculo', function (Blueprint $table) {                                                   
                $table->string('sis_carga_ponte', 1)->default('N')->nullable()->after('sis_carga_empilha');
            });
            
            Schema::table('veiculo', function (Blueprint $table) {                                                   
                $table->string('sis_carga_manual', 1)->default('N')->nullable()->after('sis_carga_ponte');
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

            if (Schema::hasColumn('veiculo', 'sis_carga_empilha')) {

                Schema::table('veiculo', function (Blueprint $table) {
                    $table->dropColumn('sis_carga_empilha');
                });

            }

            if (Schema::hasColumn('veiculo', 'sis_carga_ponte')) {

                Schema::table('veiculo', function (Blueprint $table) {
                    $table->dropColumn('sis_carga_ponte');
                });

            }

            if (Schema::hasColumn('veiculo', 'sis_carga_manual')) {

                Schema::table('veiculo', function (Blueprint $table) {
                    $table->dropColumn('sis_carga_manual');
                });

            }
            
        }
    }
}
