<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMotoristaTable003 extends Migration
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
                $table->time('hr_ini_exped')->nullable()->after('user_id');        
            });

            Schema::table('motorista', function (Blueprint $table) {
                $table->time('hr_fim_exped')->nullable()->after('hr_ini_exped');
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

            if (Schema::hasColumn('motorista', 'hr_ini_exped')) {

                Schema::table('motorista', function (Blueprint $table) {
                    $table->dropColumn('hr_ini_exped');
                });
            }

            if (Schema::hasColumn('motorista', 'hr_fim_exped')) {

                Schema::table('motorista', function (Blueprint $table) {
                    $table->dropColumn('hr_fim_exped');
                });
            }
        }
    }
}
