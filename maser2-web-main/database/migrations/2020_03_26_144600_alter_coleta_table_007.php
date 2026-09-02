<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaTable007 extends Migration
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
                $table->foreign('placa_entrega')->references('placa')->on('veiculo');
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
            
            Schema::table('coleta', function (Blueprint $table) {
                $table->dropForeign(['placa_entrega']);                    
                $table->dropIndex('coleta_placa_entrega_foreign');
            });

        }       
    }
}
