<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaFixaTable005 extends Migration
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
                $table->string('tipo_frete', 1)->default('N')->nullable()->after('aceitar_foto_rom');
            });

            Schema::table('coleta_fixa', function (Blueprint $table) {
                $table->string('autoriza_coleta', 1)->default('N')->nullable()->after('placa_coleta');
            });

            Schema::table('coleta_fixa', function (Blueprint $table) {
                $table->string('caract_coleta', 150)->nullable()->after('autoriza_coleta');
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

            if (Schema::hasColumn('coleta_fixa', 'tipo_frete')) {

                Schema::table('coleta_fixa', function (Blueprint $table) {
                    $table->dropColumn('tipo_frete');
                });
            }

            if (Schema::hasColumn('coleta_fixa', 'autoriza_coleta')) {

                Schema::table('coleta_fixa', function (Blueprint $table) {
                    $table->dropColumn('autoriza_coleta');
                });
            }

            if (Schema::hasColumn('coleta_fixa', 'caract_coleta')) {

                Schema::table('coleta_fixa', function (Blueprint $table) {
                    $table->dropColumn('caract_coleta');
                });
            }
        }
    }
}
