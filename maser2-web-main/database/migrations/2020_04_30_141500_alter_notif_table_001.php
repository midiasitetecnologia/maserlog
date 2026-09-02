<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterNotifTable001 extends Migration
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
                $table->string('titulo', 50)->nullable()->after('evento');
                $table->text('texto')->nullable()->after('titulo');
                $table->string('lida', 1)->nullable()->after('recebida');
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

            if (Schema::hasColumn('notif', 'titulo')) {                

                Schema::table('notif', function (Blueprint $table) {
                    $table->dropColumn('titulo');
                });
            }

            if (Schema::hasColumn('notif', 'texto')) {                

                Schema::table('notif', function (Blueprint $table) {
                    $table->dropColumn('texto');
                });
            }

            if (Schema::hasColumn('notif', 'lida')) {                

                Schema::table('notif', function (Blueprint $table) {
                    $table->dropColumn('lida');
                });
            }
        }
    }
}
