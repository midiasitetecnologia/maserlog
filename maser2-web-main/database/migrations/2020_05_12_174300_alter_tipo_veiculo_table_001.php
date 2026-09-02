<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTipoVeiculoTable001 extends Migration
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
                $table->string('classe', 1)->default('M')->nullable()->after('descricao');
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

            if (Schema::hasColumn('tipo_veiculo', 'classe')) {                

                Schema::table('tipo_veiculo', function (Blueprint $table) {
                    $table->dropColumn('classe');
                });
            }
        }
    }
}
