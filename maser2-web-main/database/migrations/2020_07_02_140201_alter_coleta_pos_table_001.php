<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaPosTable001 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('coleta_pos')) {

            Schema::table('coleta_pos', function (Blueprint $table) {
                $table->decimal('distancia', 9, 3)->nullable()->after('geo_lng');
                $table->time('tempo')->nullable()->after('distancia');
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
        if (Schema::hasTable('coleta_pos')) {

            if (Schema::hasColumn('coleta_pos', 'distancia')) {

                Schema::table('coleta_pos', function (Blueprint $table) {
                    $table->dropColumn('distancia');
                });
            }

            if (Schema::hasColumn('coleta_pos', 'tempo')) {

                Schema::table('coleta_pos', function (Blueprint $table) {
                    $table->dropColumn('tempo');
                });
            }
        }
    }
}
