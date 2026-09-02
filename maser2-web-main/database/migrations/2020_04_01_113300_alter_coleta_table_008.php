<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaTable008 extends Migration
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
                $table->string('img_carga', 50)->nullable()->after('solic_origem_id');
            });           

            Schema::table('coleta', function (Blueprint $table) {
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
        if (Schema::hasTable('coleta')) {
            
            if (Schema::hasColumn('coleta', 'img_carga')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('img_carga');
                });
            }

            if (Schema::hasColumn('coleta', 'ocup_veiculo')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('ocup_veiculo');
                });
            }

        }       
    }
}
