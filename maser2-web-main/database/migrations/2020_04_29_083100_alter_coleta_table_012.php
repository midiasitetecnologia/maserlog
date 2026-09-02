<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaTable012 extends Migration
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
                $table->decimal('distancia_coleta', 9, 3)->nullable()->after('tempo_estimado');
                $table->time('tempo_coleta')->nullable()->after('distancia_coleta');
                $table->decimal('distancia_entrega', 9, 3)->nullable()->after('tempo_coleta');
                $table->time('tempo_entrega')->nullable()->after('distancia_entrega');                
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

            if (Schema::hasColumn('coleta', 'distancia_coleta')) {                

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('distancia_coleta');
                });
            }

            if (Schema::hasColumn('coleta', 'tempo_coleta')) {                

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('tempo_coleta');
                });
            }

            if (Schema::hasColumn('coleta', 'distancia_entrega')) {                

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('distancia_entrega');
                });
            }

            if (Schema::hasColumn('coleta', 'tempo_entrega')) {                

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('tempo_entrega');
                });
            }
        }
    }
}
