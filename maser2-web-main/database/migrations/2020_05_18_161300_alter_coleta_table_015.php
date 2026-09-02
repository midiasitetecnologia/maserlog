<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaTable015 extends Migration
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
                $table->unsignedInteger('cod_tipo_veiculo_nec')->nullable()->after('hr_sai_coleta');
                $table->string('baldeada', 1)->nullable()->after('placa_baldeacao');
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

            if (Schema::hasColumn('coleta', 'cod_tipo_veiculo_nec')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('cod_tipo_veiculo_nec');
                });
            }

            if (Schema::hasColumn('coleta', 'baldeada')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('baldeada');
                });
            }
        }
    }
}
