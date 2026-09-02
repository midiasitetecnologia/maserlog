<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaFixaTable002 extends Migration
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
                $table->string('sis_carga', 1)->nullable()->after('cod_tipo_veiculo');
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

            if (Schema::hasColumn('coleta_fixa', 'sis_carga')) {

                Schema::table('coleta_fixa', function (Blueprint $table) {
                    $table->dropColumn('sis_carga');
                });
            }
        }
               
    }
}
