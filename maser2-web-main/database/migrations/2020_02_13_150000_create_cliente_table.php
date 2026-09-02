<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClienteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('cliente')) {

            Schema::create('cliente', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');                
                $table->unsignedInteger('empresa')->nullable();
                $table->unsignedInteger('codigo')->nullable();
                $table->string('tipo_pessoa', 1)->default('J')->nullable();
                $table->string('nome', 80)->nullable();
                $table->string('fantasia', 60)->nullable();
                $table->string('cpf_cnpj', 25)->nullable();
                $table->string('fone', 20)->nullable();
                $table->string('cep', 15)->nullable();
                $table->string('endereco', 80)->nullable();
                $table->string('bairro', 50)->nullable();
                $table->string('cidade', 50)->nullable();
                $table->string('uf', 2)->nullable();
                $table->decimal('geo_lat', 10, 8)->nullable();
                $table->decimal('geo_lng', 11, 8)->nullable();
                $table->time('hr_ini_coleta_man')->nullable();
                $table->time('hr_fim_coleta_man')->nullable();
                $table->time('hr_ini_coleta_tar')->nullable();
                $table->time('hr_fim_coleta_tar')->nullable();
                $table->time('hr_ini_entrega_man')->nullable();
                $table->time('hr_fim_entrega_man')->nullable();
                $table->time('hr_ini_entrega_tar')->nullable();
                $table->time('hr_fim_entrega_tar')->nullable();
                $table->date('dt_alt_cad')->nullable();
                $table->time('hr_alt_cad')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('ass_user_id')->nullable();
                $table->timestamps();

                $table->unique(['empresa', 'codigo']);
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
        Schema::dropIfExists('cliente');
    }
}
