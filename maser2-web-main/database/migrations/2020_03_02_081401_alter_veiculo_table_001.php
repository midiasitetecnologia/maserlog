<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterVeiculoTable001 extends Migration
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
                $table->string('usar_gps', 1)->default('M')->nullable()->after('ativo');
            });

            Schema::table('veiculo', function (Blueprint $table) {                                                   
                $table->decimal('geo_lat', 10, 8)->nullable()->after('usar_gps');
            });
            
            Schema::table('veiculo', function (Blueprint $table) {                                                   
                $table->decimal('geo_lng', 11, 8)->nullable()->after('geo_lat');
            });

            Schema::table('veiculo', function (Blueprint $table) {                                                   
                $table->timestamp('dt_geopos')->nullable()->after('geo_lng');
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

            if (Schema::hasColumn('veiculo', 'usar_gps')) {

                Schema::table('veiculo', function (Blueprint $table) {
                    $table->dropColumn('usar_gps');
                });

            }

            if (Schema::hasColumn('veiculo', 'geo_lat')) {

                Schema::table('veiculo', function (Blueprint $table) {
                    $table->dropColumn('geo_lat');
                });

            }

            if (Schema::hasColumn('veiculo', 'geo_lng')) {

                Schema::table('veiculo', function (Blueprint $table) {
                    $table->dropColumn('geo_lng');
                });

            }

            if (Schema::hasColumn('veiculo', 'dt_geopos')) {

                Schema::table('veiculo', function (Blueprint $table) {
                    $table->dropColumn('dt_geopos');
                });

            }
            
        }
    }
}
