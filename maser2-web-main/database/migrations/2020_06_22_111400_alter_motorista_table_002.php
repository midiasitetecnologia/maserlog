<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMotoristaTable002 extends Migration
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
                $table->time('hr_ini_login')->nullable()->after('user_id');        
            });

            Schema::table('motorista', function (Blueprint $table) {
                $table->time('hr_fim_login')->nullable()->after('hr_ini_login');
            });

            Schema::table('motorista', function (Blueprint $table) {
                $table->string('auto_logoff', 1)->default('N')->nullable()->after('hr_fim_login');                
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

            if (Schema::hasColumn('motorista', 'hr_ini_login')) {

                Schema::table('motorista', function (Blueprint $table) {
                    $table->dropColumn('hr_ini_login');
                });
            }

            if (Schema::hasColumn('motorista', 'hr_fim_login')) {

                Schema::table('motorista', function (Blueprint $table) {
                    $table->dropColumn('hr_fim_login');
                });
            }

            if (Schema::hasColumn('motorista', 'auto_logoff')) {

                Schema::table('motorista', function (Blueprint $table) {
                    $table->dropColumn('auto_logoff');
                });
            }
        }
    }
}
