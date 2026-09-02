<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSysCfgTable002 extends Migration
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
                $table->binary('office_area')->nullable()->after('url_redirect');
            });

            Schema::table('sys_cfg', function ($table) {
                $table->binary('garage_area')->nullable()->after('office_area');
            });

            Schema::table('sys_cfg', function ($table) {
                $table->binary('pavilion_area')->nullable()->after('garage_area');
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

            if (Schema::hasColumn('sys_cfg', 'office_area')) {

                Schema::table('sys_cfg', function (Blueprint $table) {
                    $table->dropColumn('office_area');
                });
            }

            if (Schema::hasColumn('sys_cfg', 'garage_area')) {

                Schema::table('sys_cfg', function (Blueprint $table) {
                    $table->dropColumn('garage_area');
                });
            }

            if (Schema::hasColumn('sys_cfg', 'pavilion_area')) {

                Schema::table('sys_cfg', function (Blueprint $table) {
                    $table->dropColumn('pavilion_area');
                });
            }
        }
    }
}
