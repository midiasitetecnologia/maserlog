<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaFixaTable004 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('coleta_fixa')) {

            Schema::table('coleta_fixa', function (Blueprint $table) {
                $table->string('aceitar_foto_rom', 1)->nullable()->after('receber_nf_frete');
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
        if (Schema::hasTable('coleta_fixa')) {

            if (Schema::hasColumn('coleta_fixa', 'aceitar_foto_rom')) {

                Schema::table('coleta_fixa', function (Blueprint $table) {
                    $table->dropColumn('aceitar_foto_rom');
                });
            }
        }
    }
}
