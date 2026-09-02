<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterNotifTable002 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('notif')) {

            if (Schema::hasColumn('notif', 'recebida')) {

                Schema::table('notif', function (Blueprint $table) {
                    $table->dropColumn('recebida');
                });
            }
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
                $table->string('recebida', 1)->nullable()->after('reg_id');
            });
        }
    }
}
