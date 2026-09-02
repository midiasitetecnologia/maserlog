<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateColetaFixaBloqTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('coleta_fixa_bloq')) {

            Schema::create('coleta_fixa_bloq', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');                
                $table->unsignedInteger('coleta_fixa_id')->nullable();
                $table->date('dt_ini')->nullable();
                $table->date('dt_fim')->nullable();
                $table->string('observ')->nullable();
                $table->unsignedBigInteger('ass_user_id')->nullable();
                $table->timestamps();                

                $table->foreign('coleta_fixa_id')->references('id')->on('coleta_fixa');
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
        Schema::dropIfExists('coleta_fixa_bloq');
    }
}
