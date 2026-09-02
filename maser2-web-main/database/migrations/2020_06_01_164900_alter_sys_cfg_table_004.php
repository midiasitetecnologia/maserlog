<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSysCfgTable004 extends Migration
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
                $table->string('gerar_coletas_fixas', 1)->nullable()->after('url_redirect');
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

            if (Schema::hasColumn('sys_cfg', 'gerar_coletas_fixas')) {

                Schema::table('sys_cfg', function (Blueprint $table) {
                    $table->dropColumn('gerar_coletas_fixas');
                });
            }
        }
    }
}
