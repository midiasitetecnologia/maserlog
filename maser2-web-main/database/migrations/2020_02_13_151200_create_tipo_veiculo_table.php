<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTipoVeiculoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tipo_veiculo')) {

            Schema::create('tipo_veiculo', function (Blueprint $table) {
                $table->engine = 'InnoDB';                    
                $table->unsignedInteger('codigo')->primary();
                $table->string('descricao', 60)->nullable();
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
        Schema::dropIfExists('tipo_veiculo');
    }
}
