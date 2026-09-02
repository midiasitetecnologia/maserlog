<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSysCfgTable005 extends Migration
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
                $table->string('dia_coletas_fixas', 1)->default('S')->nullable()->after('gerar_coletas_fixas');
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

            if (Schema::hasColumn('sys_cfg', 'dia_coletas_fixas')) {

                Schema::table('sys_cfg', function (Blueprint $table) {
                    $table->dropColumn('dia_coletas_fixas');
                });
            }
        }
    }
}
