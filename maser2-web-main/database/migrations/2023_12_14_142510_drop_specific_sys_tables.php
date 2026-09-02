<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropSpecificSysTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('sys_permission');        
        Schema::dropIfExists('sys_user_group');
        Schema::dropIfExists('sys_group_resource');        
        Schema::dropIfExists('sys_resource');
        Schema::dropIfExists('sys_group');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sys_permission');        
        Schema::dropIfExists('sys_user_group');
        Schema::dropIfExists('sys_group_resource');        
        Schema::dropIfExists('sys_resource');
        Schema::dropIfExists('sys_group');
    }
}
