<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        if (!Schema::hasTable('user_log')) {

            Schema::create('user_log', function (Blueprint $table) {
            $table->engine = 'InnoDB';            
            $table->increments('id');
            $table->string('uuid', 36)->nullable();            
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('date_time')->nullable();
            $table->string('table_name', 30)->nullable();
            $table->string('operation', 6)->nullable();
            $table->string('pk', 30)->nullable();
            $table->string('pk_value', 30)->nullable();
            $table->string('client_addr')->nullable();
            $table->string('column_name', 30)->nullable();
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->binary('old_value_blob')->nullable();
            $table->binary('new_value_blob')->nullable();            
            });
            
        }

        if (Schema::hasColumn('user_log', 'date_time'))
        {
            DB::statement('ALTER TABLE user_log MODIFY date_time TIMESTAMP(6) NULL DEFAULT NULL'); 
        }        
        
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
     public function down()
    {
        Schema::dropIfExists('user_log');
    }
}
