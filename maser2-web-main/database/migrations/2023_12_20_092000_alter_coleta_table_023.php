<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaTable023 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('coleta')) {

            Schema::table('coleta', function (Blueprint $table) {
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
        if (Schema::hasTable('coleta')) {

            if (Schema::hasColumn('coleta', 'ocultar_resumo')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('ocultar_resumo');
                });
            }
        }
    }
}
