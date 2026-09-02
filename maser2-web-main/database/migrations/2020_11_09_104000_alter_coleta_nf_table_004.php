<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaNfTable004 extends Migration
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
                $table->string('observ')->nullable()->after('solic_distrib_id');
            });

            Schema::table('coleta_nf', function (Blueprint $table) {
                $table->string('origem_reg', 2)->nullable()->after('observ');
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

            if (Schema::hasColumn('coleta_nf', 'observ')) {

                Schema::table('coleta_nf', function (Blueprint $table) {
                    $table->dropColumn('observ');
                });
            }

            if (Schema::hasColumn('coleta_nf', 'origem_reg')) {

                Schema::table('coleta_nf', function (Blueprint $table) {
                    $table->dropColumn('origem_reg');
                });
            }
        }
    }
}
