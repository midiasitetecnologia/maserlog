<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaTable002 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (Schema::hasTable('coleta')) {                     

            if (Schema::hasColumn('coleta', 'geo_lat_coleta')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('geo_lat_coleta');
                });

            }

            if (Schema::hasColumn('coleta', 'geo_lng_coleta')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('geo_lng_coleta');
                });

            }

            if (Schema::hasColumn('coleta', 'geo_lat_entrega')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('geo_lat_entrega');
                });

            }

            if (Schema::hasColumn('coleta', 'geo_lng_entrega')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('geo_lng_entrega');
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
        if (Schema::hasTable('coleta')) {                     

            Schema::table('coleta', function (Blueprint $table) {                                                   
                $table->decimal('geo_lat_coleta', 10, 8)->nullable()->after('km_final');
            });

            Schema::table('coleta', function (Blueprint $table) {                                                   
                $table->decimal('geo_lng_coleta', 11, 8)->nullable()->after('geo_lat_coleta');
            });
            
            Schema::table('coleta', function (Blueprint $table) {                                                   
                $table->decimal('geo_lat_entrega', 10, 8)->nullable()->after('geo_lng_coleta');
            });

            Schema::table('coleta', function (Blueprint $table) {                                                   
                $table->decimal('geo_lng_entrega', 11, 8)->nullable()->after('geo_lat_entrega');
            });
            
        }
    }
}
