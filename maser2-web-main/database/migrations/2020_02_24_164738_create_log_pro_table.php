<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogProTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('log_pro')) {

            Schema::create('log_pro', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('evento', 100)->index()->nullable();
                $table->string('tipo', 1)->nullable();
                $table->string('msg')->nullable();
                $table->binary('err')->nullable();
                $table->string('status', 1)->nullable();
                $table->unsignedInteger('proc_id')->index()->nullable();
                $table->timestamps();

                $table->index(['tipo', 'created_at']);
                $table->index(['tipo', 'status', 'created_at']);
                $table->index(['tipo', 'evento', 'created_at']);
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
        Schema::dropIfExists('log_pro');
    }
}
