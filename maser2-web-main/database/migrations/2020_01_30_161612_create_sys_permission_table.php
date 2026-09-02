<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSysPermissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('sys_permission')) {

            Schema::create('sys_permission', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');                
                $table->unsignedInteger('sys_resource_id')->nullable();
                $table->unsignedInteger('sys_group_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('p_list', 1)->default('N')->nullable();
                $table->string('p_view', 1)->default('N')->nullable();
                $table->string('p_create', 1)->default('N')->nullable();
                $table->string('p_update', 1)->default('N')->nullable();
                $table->string('p_delete', 1)->default('N')->nullable();                
                $table->unsignedBigInteger('ass_user_id')->nullable();
                $table->timestamps();

                $table->foreign('sys_resource_id')->references('id')->on('sys_resource');
                $table->foreign('sys_group_id')->references('id')->on('sys_group');
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
        Schema::dropIfExists('sys_permission');
    }
}
