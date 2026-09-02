<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMotoristaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('motorista')) {

            Schema::create('motorista', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('cpf', 15)->unique()->nullable();
                $table->string('nome', 60)->nullable();
                $table->string('celular', 20)->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('ativo', 1)->default('S')->nullable();
                $table->decimal('geo_lat', 10, 8)->nullable();
                $table->decimal('geo_lng', 11, 8)->nullable();
                $table->timestamp('dt_geopos')->nullable();
                $table->string('id_disp', 100)->nullable();
                $table->string('logado', 1)->default('N')->nullable();
                $table->timestamp('dt_logado')->nullable();
                $table->unsignedBigInteger('ass_user_id')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('motorista');
    }
}
