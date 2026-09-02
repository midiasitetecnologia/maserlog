<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaFixaTable006 extends Migration
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
                $table->string('ocultar_resumo', 1)->default('N')->nullable()->after('aceitar_foto_rom');
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

            if (Schema::hasColumn('coleta_fixa', 'ocultar_resumo')) {

                Schema::table('coleta_fixa', function (Blueprint $table) {
                    $table->dropColumn('ocultar_resumo');
                });
            }
        }
    }
}
