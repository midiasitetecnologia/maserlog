<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClienteTable001 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('cliente')) {

            Schema::table('cliente', function (Blueprint $table) {                
                $table->string('local_distrib', 1)->nullable()->after('hr_fim_entrega_tar');
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
        if (Schema::hasTable('cliente')) {

            if (Schema::hasColumn('cliente', 'local_distrib')) {

                Schema::table('cliente', function (Blueprint $table) {
                    $table->dropColumn('local_distrib');
                });
            }
        }
    }
}
