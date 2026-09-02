<?php

use Illuminate\Database\Migrations\Migration;

class AlterTipoVeiculoGravarLogAfterUpdateTrigger003 extends Migration
{
    /**
     *
     * ATENÇÃO: Sempre que um novo campo for inserido ou alterado da trigger, devemos identificar com um comentário com o padrão do MySql 
     *          acima do campo e com a data da alteração e assinatura de quem fez a alteração.
     * 
     * Exemplo: -- Novo campo "email". 24/05/2017 - Ricardo Fochesatto.
     *
     *
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS tr_tipo_veiculo_gravar_log_after_update');
        DB::unprepared('
            CREATE TRIGGER tr_tipo_veiculo_gravar_log_after_update AFTER UPDATE ON tipo_veiculo FOR EACH ROW
                BEGIN
                    
                    -- As variaveis @uuid, @date_time serve como identificação para saber oque foi alterado nesse lote de dados.
                    SET @uuid = UUID(); 
                    SET @date_time = current_timestamp(6);
                    SET @client_addr = get_client_addr();

                    IF (IFNULL(OLD.descricao, "") != IFNULL(NEW.descricao, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "tipo_veiculo", "update", "codigo", NEW.codigo, @client_addr, "descricao", OLD.descricao, 
                          NEW.descricao
                        );                    
                    END IF;

                    IF (IFNULL(OLD.classe, "") != IFNULL(NEW.classe, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "tipo_veiculo", "update", "codigo", NEW.codigo, @client_addr, "classe", OLD.classe, 
                          NEW.classe
                        );                    
                    END IF;

                    IF (IFNULL(OLD.dur_prev_atend, "") != IFNULL(NEW.dur_prev_atend, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "tipo_veiculo", "update", "codigo", NEW.codigo, @client_addr, "dur_prev_atend", OLD.dur_prev_atend, 
                          NEW.dur_prev_atend
                        );                    
                    END IF;

                    IF (IFNULL(OLD.tempo_desloc_pavilhao, "") != IFNULL(NEW.tempo_desloc_pavilhao, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "tipo_veiculo", "update", "codigo", NEW.codigo, @client_addr, "tempo_desloc_pavilhao", OLD.tempo_desloc_pavilhao, 
                          NEW.tempo_desloc_pavilhao
                        );                    
                    END IF;

                END
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS tr_tipo_veiculo_gravar_log_after_update');
    }
}
