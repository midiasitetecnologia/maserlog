<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaTable009 extends Migration
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
                $table->decimal('distancia_real', 9, 3)->nullable()->after('tempo_estimado');
            });

            Schema::table('coleta', function (Blueprint $table) {
                $table->time('tempo_real')->nullable()->after('distancia_real');
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

            if (Schema::hasColumn('coleta', 'distancia_real')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('distancia_real');
                });
            }

            if (Schema::hasColumn('coleta', 'tempo_real')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('tempo_real');
                });
            }
        }
    }
}
