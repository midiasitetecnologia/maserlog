<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaNfTable003 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('coleta_nf')) {

            Schema::table('coleta_nf', function (Blueprint $table) {
                $table->unsignedInteger('solic_distrib_id')->nullable()->after('img_recibo');
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
        if (Schema::hasTable('coleta_nf')) {

            if (Schema::hasColumn('coleta_nf', 'solic_distrib_id')) {

                Schema::table('coleta_nf', function (Blueprint $table) {
                    $table->dropColumn('solic_distrib_id');
                });
            }
        }
    }
}
