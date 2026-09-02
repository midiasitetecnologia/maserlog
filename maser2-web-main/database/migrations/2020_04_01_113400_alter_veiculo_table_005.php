<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterVeiculoTable005 extends Migration
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
                $table->string('img_carga', 50)->nullable()->after('dt_geopos');
            });           

            Schema::table('veiculo', function (Blueprint $table) {
                $table->smallInteger('ocup_veiculo')->nullable()->after('img_carga');
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
        if (Schema::hasTable('veiculo')) {

            if (Schema::hasColumn('veiculo', 'img_carga')) {

                Schema::table('veiculo', function (Blueprint $table) {
                    $table->dropColumn('img_carga');
                });
            }

            if (Schema::hasColumn('veiculo', 'ocup_veiculo')) {

                Schema::table('veiculo', function (Blueprint $table) {
                    $table->dropColumn('ocup_veiculo');
                });
            }
        }
    }
}
