<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaLogTable001 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('coleta_log')) {

            Schema::table('coleta_log', function (Blueprint $table) {
                $table->dropForeign(['coleta_id']);                    
                $table->dropIndex('coleta_log_coleta_id_foreign');
                $table->foreign('coleta_id')->references('id')->on('coleta')->onDelete('cascade');
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
        if (Schema::hasTable('coleta_log')) {
            
            Schema::table('coleta_log', function (Blueprint $table) {
                $table->dropForeign(['coleta_id']);                    
                $table->dropIndex('coleta_log_coleta_id_foreign');
                $table->foreign('coleta_id')->references('id')->on('coleta');
            });

        }       
    }
}
