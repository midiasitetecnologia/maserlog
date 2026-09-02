<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClienteTable002 extends Migration
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
                $table->string('solicitar_coletas', 1)->nullable()->after('local_distrib');
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

            if (Schema::hasColumn('cliente', 'solicitar_coletas')) {

                Schema::table('cliente', function (Blueprint $table) {
                    $table->dropColumn('solicitar_coletas');
                });
            }
        }
    }
}
