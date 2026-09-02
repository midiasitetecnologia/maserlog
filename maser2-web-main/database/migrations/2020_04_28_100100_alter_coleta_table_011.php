<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaTable011 extends Migration
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
                $table->unsignedInteger('coleta_fixa_id')->nullable()->after('coleta_fixa');

                $table->foreign('coleta_fixa_id')->references('id')->on('coleta_fixa');
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

            if (Schema::hasColumn('coleta', 'coleta_fixa_id')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropForeign(['coleta_fixa_id']);                    
                    $table->dropIndex('coleta_coleta_fixa_id_foreign');
                });

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('coleta_fixa_id');
                });
            }
        }
    }
}
