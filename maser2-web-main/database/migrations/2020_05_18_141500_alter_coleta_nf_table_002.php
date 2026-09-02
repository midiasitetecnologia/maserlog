<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaNfTable002 extends Migration
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
                $table->string('dig_cnpj', 2)->nullable()->after('volumes');
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
            
            if (Schema::hasColumn('coleta_nf', 'dig_cnpj')) {

                Schema::table('coleta_nf', function (Blueprint $table) {
                    $table->dropColumn('dig_cnpj');
                });
            }
            
        }
    }
}
