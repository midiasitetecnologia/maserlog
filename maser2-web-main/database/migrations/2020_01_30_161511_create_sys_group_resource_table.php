<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSysGroupResourceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('sys_group_resource')) {

            Schema::create('sys_group_resource', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->unsignedInteger('sys_group_id')->nullable();                
                $table->unsignedInteger('sys_resource_id')->nullable();                
                $table->unsignedBigInteger('ass_user_id')->nullable();
                $table->timestamps();

                $table->foreign('sys_group_id')->references('id')->on('sys_group');
                $table->foreign('sys_resource_id')->references('id')->on('sys_resource');                
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
        Schema::dropIfExists('sys_group_resource');
    }
}
