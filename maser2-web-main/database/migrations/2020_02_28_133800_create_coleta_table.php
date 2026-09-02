<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateColetaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('coleta')) {

            Schema::create('coleta', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');                
                $table->unsignedInteger('empresa')->nullable();
                $table->unsignedInteger('numero')->nullable();
                $table->date('data_cad')->nullable();
                $table->time('hora_cad')->nullable();
                $table->unsignedInteger('cod_cliente')->nullable();
                $table->date('dt_prev_coleta')->nullable();
                $table->time('hr_prev_coleta')->nullable();
                $table->date('dt_prev_entrega')->nullable();
                $table->time('hr_prev_entrega')->nullable();
                $table->string('entrega_urgente', 1)->default('N')->nullable();
                $table->unsignedInteger('cod_loc_coleta')->nullable();
                $table->unsignedInteger('cod_loc_entrega')->nullable();
                $table->decimal('peso', 15, 3)->nullable();
                $table->string('solicitante', 80)->nullable();
                $table->unsignedInteger('volumes')->nullable();
                $table->string('especie', 80)->nullable();
                $table->string('tipo_carga', 1)->nullable();
                $table->decimal('alt_carga', 9, 3)->nullable();
                $table->decimal('larg_carga', 9, 3)->nullable();
                $table->decimal('comp_carga', 9, 3)->nullable();
                $table->unsignedInteger('cod_tipo_veiculo')->nullable();
                $table->string('placa_coleta', 8)->nullable();
                $table->string('caract_coleta', 150)->nullable();
                $table->unsignedInteger('motor_coleta_id')->nullable();
                $table->string('coleta_fixa', 1)->nullable();
                $table->binary('obs_coleta')->nullable();
                $table->date('dt_efet_coleta')->nullable();
                $table->time('hr_cheg_coleta')->nullable();
                $table->time('hr_atend_coleta')->nullable();
                $table->time('hr_sai_coleta')->nullable();
                $table->string('placa_entrega', 8)->nullable();                
                $table->unsignedInteger('motor_entrega_id')->nullable();
                $table->date('dt_efet_entrega')->nullable();
                $table->time('hr_cheg_entrega')->nullable();
                $table->time('hr_atend_entrega')->nullable();
                $table->time('hr_sai_entrega')->nullable();
                $table->string('recebedor', 100)->nullable();
                $table->string('receber_nf_frete', 1)->nullable();
                $table->unsignedInteger('km_inicial')->nullable();
                $table->unsignedInteger('km_final')->nullable();
                $table->decimal('geo_lat_coleta', 10, 8)->nullable();
                $table->decimal('geo_lng_coleta', 11, 8)->nullable();
                $table->decimal('geo_lat_entrega', 10, 8)->nullable();
                $table->decimal('geo_lng_entrega', 11, 8)->nullable();
                $table->decimal('distancia_km', 9, 3)->nullable();
                $table->time('tempo_estimado')->nullable();
                $table->time('dur_prev_coleta')->nullable();
                $table->time('dur_prev_entrega')->nullable();
                $table->string('instrucao', 2)->nullable();
                $table->string('txt_instrucao')->nullable();
                $table->string('descarga_coleta', 1)->nullable();
                $table->timestamp('dt_descarga_coleta')->nullable();
                $table->string('placa_baldeacao', 8)->nullable();
                $table->string('status', 2)->nullable();
                $table->string('mot_nao_coleta', 2)->nullable();
                $table->string('obs_nao_coleta')->nullable();
                $table->unsignedInteger('solic_origem_id')->nullable();
                $table->string('origem_reg', 2)->nullable();
                $table->string('coleta_export', 1)->nullable();
                $table->timestamp('dt_coleta_export')->nullable();
                $table->string('entrega_export', 1)->nullable();
                $table->timestamp('dt_entrega_export')->nullable();
                $table->unsignedBigInteger('ass_user_id')->nullable();
                $table->timestamps();

                $table->unique(['empresa', 'numero']);
                $table->foreign('placa_coleta')->references('placa')->on('veiculo');
                $table->foreign('motor_coleta_id')->references('id')->on('motorista');
                $table->foreign('placa_baldeacao')->references('placa')->on('veiculo');
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
        Schema::dropIfExists('coleta');
    }
}
