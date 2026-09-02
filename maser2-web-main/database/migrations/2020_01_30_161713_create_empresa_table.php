<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmpresaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('empresa')) {

            Schema::create('empresa', function (Blueprint $table) {
                $table->engine = 'InnoDB';                
                $table->unsignedInteger('codigo')->primary();
                $table->string('nome', 60)->nullable();
                $table->string('sigla', 3)->nullable();
                $table->string('cor_fonte', 7)->nullable();
                $table->string('cor_fundo', 7)->nullable();
                $table->string('icone', 20)->nullable();                
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
        Schema::dropIfExists('empresa');
    }
}
