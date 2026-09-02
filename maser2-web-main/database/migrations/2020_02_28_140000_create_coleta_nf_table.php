<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateColetaNfTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('coleta_nf')) {

            Schema::create('coleta_nf', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');                
                $table->unsignedInteger('coleta_id')->nullable();                
                $table->string('cod_barras', 50)->nullable();
                $table->unsignedInteger('serie')->nullable();
                $table->unsignedInteger('numero')->nullable();
                $table->decimal('valor', 15, 2)->nullable();
                $table->unsignedInteger('volumes')->nullable();
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
        Schema::dropIfExists('coleta_nf');
    }
}
