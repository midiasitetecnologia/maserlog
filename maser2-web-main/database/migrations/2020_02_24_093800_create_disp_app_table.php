<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDispAppTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('disp_app')) {
            
            Schema::create('disp_app', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->string('id_disp', 100)->primary()->unique();
            $table->string('descricao')->nullable();
            $table->string('plataforma', 15)->nullable();
            $table->string('versao_so', 50)->nullable();
            $table->string('versao_app', 15)->nullable();
            $table->string('push_token')->nullable();            
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
        Schema::dropIfExists('disp_app');
    }
}
