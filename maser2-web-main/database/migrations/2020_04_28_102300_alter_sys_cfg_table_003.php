<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSysCfgTable003 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (Schema::hasTable('sys_cfg')) {

            Schema::table('sys_cfg', function ($table) {
                $table->decimal('geo_lat_pavilion', 10, 8)->nullable()->after('pavilion_area');
                $table->decimal('geo_lng_pavilion', 11, 8)->nullable()->after('geo_lat_pavilion');
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
        if (Schema::hasTable('sys_cfg')) {

            if (Schema::hasColumn('sys_cfg', 'geo_lat_pavilion')) {

                Schema::table('sys_cfg', function (Blueprint $table) {
                    $table->dropColumn('geo_lat_pavilion');
                });
            }

            if (Schema::hasColumn('sys_cfg', 'geo_lng_pavilion')) {

                Schema::table('sys_cfg', function (Blueprint $table) {
                    $table->dropColumn('geo_lng_pavilion');
                });
            }
        }
    }
}
