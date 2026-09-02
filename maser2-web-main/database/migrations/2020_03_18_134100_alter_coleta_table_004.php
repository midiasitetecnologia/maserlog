<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaTable004 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('coleta')) {

            if (Schema::hasColumn('coleta', 'km_inicial')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('km_inicial');
                });
            }

            if (Schema::hasColumn('coleta', 'km_final')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('km_final');
                });
            }

            if (Schema::hasColumn('coleta', 'km_ini_entrega')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('km_ini_entrega');
                });
            }

            if (Schema::hasColumn('coleta', 'km_fim_entrega')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('km_fim_entrega');
                });
            }
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
                $table->unsignedInteger('km_inicial')->nullable()->after('dt_desloca_coleta');
            });

            Schema::table('coleta', function (Blueprint $table) {
                $table->unsignedInteger('km_final')->nullable()->after('km_inicial');
            });

            Schema::table('coleta', function (Blueprint $table) {
                $table->unsignedInteger('km_ini_entrega')->nullable()->after('dt_desloca_entrega');
            });

            Schema::table('coleta', function (Blueprint $table) {
                $table->unsignedInteger('km_fim_entrega')->nullable()->after('km_ini_entrega');
            });
        }
    }
}
