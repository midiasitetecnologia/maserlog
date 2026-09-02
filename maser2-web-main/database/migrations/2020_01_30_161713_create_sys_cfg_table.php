<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSysCfgTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('sys_cfg')) {

            Schema::create('sys_cfg', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id'); 
                $table->string('status', 2)->nullable();
                $table->timestamp('dt_ini_status')->nullable();
                $table->timestamp('dt_fim_status')->nullable();                
                $table->binary('msg_status')->nullable();
                $table->string('url_redirect')->nullable();                
                $table->unsignedBigInteger('ass_user_id')->nullable();
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
        Schema::dropIfExists('sys_cfg');
    }
}
