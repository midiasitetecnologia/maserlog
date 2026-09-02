<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('users')) {

            Schema::create('users', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->bigIncrements('id');
                $table->string('name', 100)->nullable();
                $table->string('email', 100)->unique()->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password', 100)->nullable();
                $table->rememberToken();
                $table->string('user_type', 1)->default('U')->nullable();
                $table->unsignedInteger('cliente_id')->nullable();
                $table->string('active', 1)->default('N')->nullable();
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
        Schema::dropIfExists('users');
    }
}
