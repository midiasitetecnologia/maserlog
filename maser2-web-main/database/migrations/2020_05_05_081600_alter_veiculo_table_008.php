<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterVeiculoTable008 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        if (Schema::hasTable('veiculo')) {

            Schema::table('veiculo', function (Blueprint $table) {
                $table->string('img_carga', 100)->change();
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
        //Não podemos reverter uma migration de tamanho, caso contrário vai dar erro de Truncate.
    }
}
