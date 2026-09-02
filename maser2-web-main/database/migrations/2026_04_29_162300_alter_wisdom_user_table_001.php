<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterWisdomUserTable001 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('wisdom_user')) {
            Schema::table('wisdom_user', function (Blueprint $table) {
                $table->index('created_at');
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
        if (Schema::hasTable('wisdom_user')) {
            Schema::table('wisdom_user', function (Blueprint $table) {
                $table->dropIndex(['created_at']);
            });
        }
    }
}
