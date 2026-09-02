<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVeiculoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('veiculo')) {

            Schema::create('veiculo', function (Blueprint $table) {
                $table->engine = 'InnoDB';                
                $table->string('placa', 8)->primary();
                $table->string('descricao', 60)->nullable();
                $table->unsignedInteger('cod_tipo_veiculo')->nullable();
                $table->string('milk_run', 1)->default('N')->nullable();
                $table->decimal('largura', 6, 3)->nullable();
                $table->decimal('comprimento', 6, 3)->nullable();
                $table->decimal('altura', 6, 3)->nullable();
                $table->decimal('cap_cub', 9, 3)->nullable();
                $table->decimal('cap_kg', 9, 3)->nullable();
                $table->unsignedInteger('km_atual')->nullable();
                $table->unsignedInteger('motorista_id')->nullable();
                $table->string('ativo', 1)->default('S')->nullable();
                $table->unsignedBigInteger('ass_user_id')->nullable();
                $table->timestamps();

                $table->foreign('cod_tipo_veiculo')->references('codigo')->on('tipo_veiculo');
                $table->foreign('motorista_id')->references('id')->on('motorista');
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
        Schema::dropIfExists('veiculo');
    }
}
