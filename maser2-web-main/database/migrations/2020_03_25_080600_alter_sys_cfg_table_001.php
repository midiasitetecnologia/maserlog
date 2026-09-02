<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSysCfgTable001 extends Migration
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
                $table->string('url_sis_track')->nullable()->after('url_redirect');
            });

            Schema::table('sys_cfg', function ($table) {
                $table->string('user_sis_track', 50)->nullable()->after('url_sis_track');
            });

            Schema::table('sys_cfg', function ($table) {
                $table->string('pwd_sis_track', 50)->nullable()->after('user_sis_track');
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

            if (Schema::hasColumn('sys_cfg', 'url_sis_track')) {

                Schema::table('sys_cfg', function (Blueprint $table) {
                    $table->dropColumn('url_sis_track');
                });
            }

            if (Schema::hasColumn('sys_cfg', 'user_sis_track')) {

                Schema::table('sys_cfg', function (Blueprint $table) {
                    $table->dropColumn('user_sis_track');
                });
            }

            if (Schema::hasColumn('sys_cfg', 'pwd_sis_track')) {

                Schema::table('sys_cfg', function (Blueprint $table) {
                    $table->dropColumn('pwd_sis_track');
                });
            }
        }
    }
}
