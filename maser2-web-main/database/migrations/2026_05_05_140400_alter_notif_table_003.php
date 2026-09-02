<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterNotifTable003 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('notif')) {
            Schema::table('notif', function (Blueprint $table) {
                $table->index(['reg_id', 'evento']);
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
        if (Schema::hasTable('notif')) {
            Schema::table('notif', function (Blueprint $table) {
                $table->dropIndex(['reg_id_evento']);
            });
        }
    }
}
