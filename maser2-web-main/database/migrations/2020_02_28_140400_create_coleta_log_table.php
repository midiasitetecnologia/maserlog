<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateColetaLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('coleta_log')) {

            Schema::create('coleta_log', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');                
                $table->unsignedInteger('coleta_id')->nullable();                
                $table->string('tipo', 2)->nullable();
                $table->string('descricao')->nullable();
                $table->binary('texto')->nullable();
                $table->unsignedBigInteger('ass_user_id')->nullable();
                $table->timestamps();

                $table->foreign('coleta_id')->references('id')->on('coleta');
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
        Schema::dropIfExists('coleta_log');
    }
}
