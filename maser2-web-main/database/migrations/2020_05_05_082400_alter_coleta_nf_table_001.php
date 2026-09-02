<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaNfTable001 extends Migration
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
                $table->string('img_recibo', 100)->nullable()->after('volumes');
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
            
            if (Schema::hasColumn('coleta_nf', 'img_recibo')) {

                Schema::table('coleta_nf', function (Blueprint $table) {
                    $table->dropColumn('img_recibo');
                });
            }
            
        }
    }
}
