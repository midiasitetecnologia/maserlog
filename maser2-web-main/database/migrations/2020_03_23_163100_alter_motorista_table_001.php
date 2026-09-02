<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMotoristaTable001 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('motorista')) {

            Schema::table('motorista', function (Blueprint $table) {                
                $table->date('dt_alt_cad')->nullable()->after('dt_logado');        
            });

            Schema::table('motorista', function (Blueprint $table) {
                $table->time('hr_alt_cad')->nullable()->after('dt_alt_cad');
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
        if (Schema::hasTable('motorista')) {

            if (Schema::hasColumn('motorista', 'dt_alt_cad')) {

                Schema::table('motorista', function (Blueprint $table) {
                    $table->dropColumn('dt_alt_cad');
                });
            }

            if (Schema::hasColumn('motorista', 'hr_alt_cad')) {

                Schema::table('motorista', function (Blueprint $table) {
                    $table->dropColumn('hr_alt_cad');
                });
            }
        }
    }
}
