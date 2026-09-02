<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaNfTable006 extends Migration
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
                $table->string('substituida', 1)->nullable()->after('solic_destino_id');
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

            if (Schema::hasColumn('coleta_nf', 'substituida')) {

                Schema::table('coleta_nf', function (Blueprint $table) {
                    $table->dropColumn('substituida');
                });
            }
        }
    }
}
