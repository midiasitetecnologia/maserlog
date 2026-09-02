<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterWisdomTable001 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('wisdom')) {

            Schema::table('wisdom', function (Blueprint $table) {
                $table->text('texto')->change();
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
        //Não podemos reverter uma migration de tipo, caso contrário vai dar erro de collate.
    }
}
