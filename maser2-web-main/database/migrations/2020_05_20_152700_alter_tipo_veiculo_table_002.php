<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTipoVeiculoTable002 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tipo_veiculo')) {

            Schema::table('tipo_veiculo', function (Blueprint $table) {
                $table->time('dur_prev_atend')->nullable()->after('classe');
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
        if (Schema::hasTable('tipo_veiculo')) {            

            if (Schema::hasColumn('tipo_veiculo', 'dur_prev_atend')) {                

                Schema::table('tipo_veiculo', function (Blueprint $table) {
                    $table->dropColumn('dur_prev_atend');
                });
            }
        }
    }
}
