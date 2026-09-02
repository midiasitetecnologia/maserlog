<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClienteTable003 extends Migration
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
                $table->index('dt_alt_cad');
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
            Schema::table('cliente', function (Blueprint $table) {
                $table->dropIndex(['dt_alt_cad']);
            });
        }
    }
}
