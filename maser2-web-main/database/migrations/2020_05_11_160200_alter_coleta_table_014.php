<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaTable014 extends Migration
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
                $table->smallInteger('seq_atend')->nullable()->after('ocup_veiculo');
                $table->string('rota_calc', 1)->nullable()->after('seq_atend');
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

            if (Schema::hasColumn('coleta', 'seq_atend')) {                

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('seq_atend');
                });
            }

            if (Schema::hasColumn('coleta', 'rota_calc')) {                

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('rota_calc');
                });
            }
        }
    }
}
