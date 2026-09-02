<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateColetaFixaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('coleta_fixa')) {

            Schema::create('coleta_fixa', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');                
                $table->unsignedInteger('empresa')->nullable();
                $table->unsignedInteger('cod_cliente')->nullable();
                $table->string('tipo_coleta', 1)->nullable();
                $table->date('dt_ini')->nullable();
                $table->date('dt_fim')->nullable();
                $table->unsignedInteger('cod_loc_coleta')->nullable();
                $table->unsignedInteger('cod_loc_entrega')->nullable();
                $table->unsignedInteger('cod_tipo_veiculo')->nullable();
                $table->string('placa_coleta', 8)->nullable();
                $table->string('segunda', 1)->default('N')->nullable();
                $table->string('terca', 1)->default('N')->nullable();
                $table->string('quarta', 1)->default('N')->nullable();
                $table->string('quinta', 1)->default('N')->nullable();
                $table->string('sexta', 1)->default('N')->nullable();
                $table->string('sabado', 1)->default('N')->nullable();
                $table->time('hr_prev_coleta')->nullable();
                $table->string('dois_turnos', 1)->default('N')->nullable();
                $table->time('t1_hora_ini')->nullable();
                $table->time('t1_hora_fim')->nullable();
                $table->time('t2_hora_ini')->nullable();
                $table->time('t2_hora_fim')->nullable();
                $table->string('cont_cancel', 1)->default('N')->nullable();
                $table->date('dt_cancel')->nullable();
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
        Schema::dropIfExists('coleta_fixa');
    }
}
