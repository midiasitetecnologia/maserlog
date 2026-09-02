<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaTable017 extends Migration
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
                $table->string('aceitar_foto_rom', 1)->nullable()->after('receber_nf_frete');
                $table->string('img_rom_coleta', 100)->nullable()->after('ocup_veiculo');
                $table->string('img_rom_entrega', 100)->nullable()->after('img_rom_coleta');
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

            if (Schema::hasColumn('coleta', 'aceitar_foto_rom')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('aceitar_foto_rom');
                });
            }

            if (Schema::hasColumn('coleta', 'img_rom_coleta')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('img_rom_coleta');
                });
            }

            if (Schema::hasColumn('coleta', 'img_rom_entrega')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('img_rom_entrega');
                });
            }
        }
    }
}
