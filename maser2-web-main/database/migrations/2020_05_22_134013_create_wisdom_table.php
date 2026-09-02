<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWisdomTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('wisdom')) {

            Schema::create('wisdom', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id'); 
                $table->binary('texto')->nullable();
                $table->string('fonte', 100)->nullable();
                $table->timestamps();                
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
        Schema::dropIfExists('wisdom');
    }
}
