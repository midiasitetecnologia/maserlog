<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateColetaPosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('coleta_pos')) {

            Schema::create('coleta_pos', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');                
                $table->unsignedInteger('coleta_id')->nullable();                
                $table->string('status', 2)->nullable();
                $table->string('placa', 8)->nullable();
                $table->unsignedInteger('motorista_id')->nullable();
                $table->decimal('geo_lat', 10, 8)->nullable();
                $table->decimal('geo_lng', 11, 8)->nullable();
                $table->timestamps();

                $table->foreign('coleta_id')->references('id')->on('coleta');
                $table->foreign('motorista_id')->references('id')->on('motorista');
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
        Schema::dropIfExists('coleta_pos');
    }
}
