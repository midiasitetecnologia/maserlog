<?php

use Illuminate\Database\Migrations\Migration;

class AlterColetaGravarLogAfterDeleteTrigger008 extends Migration
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_coleta_gravar_log_after_delete');
        DB::unprepared('
            CREATE TRIGGER tr_coleta_gravar_log_after_delete AFTER DELETE ON coleta FOR EACH ROW
                BEGIN                
                    
                    -- As variaveis @uuid, @date_time serve como identificação para saber oque foi alterado nesse lote de dados.
                    SET @uuid = UUID(); 
                    SET @date_time = current_timestamp(6);
                    SET @client_addr = get_client_addr();

                    IF (IFNULL(OLD.empresa, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "empresa", OLD.empresa
                        );
                    END IF;

                    IF (IFNULL(OLD.numero, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "numero", OLD.numero
                        );
                    END IF;

                    IF (IFNULL(OLD.data_cad, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "data_cad", OLD.data_cad
                        );
                    END IF;

                    IF (IFNULL(OLD.hora_cad, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "hora_cad", OLD.hora_cad
                        );
                    END IF;

                    IF (IFNULL(OLD.cod_cliente, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "cod_cliente", OLD.cod_cliente
                        );
                    END IF;

                    IF (IFNULL(OLD.dt_prev_coleta, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "dt_prev_coleta", OLD.dt_prev_coleta
                        );
                    END IF;

                    IF (IFNULL(OLD.hr_prev_coleta, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "hr_prev_coleta", OLD.hr_prev_coleta
                        );
                    END IF;

                    IF (IFNULL(OLD.dt_prev_entrega, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "dt_prev_entrega", OLD.dt_prev_entrega
                        );
                    END IF;

                    IF (IFNULL(OLD.hr_prev_entrega, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "hr_prev_entrega", OLD.hr_prev_entrega
                        );
                    END IF;

                    IF (IFNULL(OLD.entrega_urgente, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "entrega_urgente", OLD.entrega_urgente
                        );
                    END IF;

                    IF (IFNULL(OLD.cod_loc_coleta, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "cod_loc_coleta", OLD.cod_loc_coleta
                        );
                    END IF;

                    IF (IFNULL(OLD.local_coleta_cmd, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "local_coleta_cmd", OLD.local_coleta_cmd
                        );
                    END IF;                    

                    IF (IFNULL(OLD.cod_loc_entrega, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "cod_loc_entrega", OLD.cod_loc_entrega
                        );
                    END IF;

                    IF (IFNULL(OLD.local_entrega_cmd, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "local_entrega_cmd", OLD.local_entrega_cmd
                        );
                    END IF;

                    IF (IFNULL(OLD.peso, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "peso", OLD.peso
                        );
                    END IF;

                    IF (IFNULL(OLD.solicitante, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "solicitante", OLD.solicitante
                        );
                    END IF;

                    IF (IFNULL(OLD.volumes, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "volumes", OLD.volumes
                        );
                    END IF;

                    IF (IFNULL(OLD.especie, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "especie", OLD.especie
                        );
                    END IF;

                    IF (IFNULL(OLD.sis_carga, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "sis_carga", OLD.sis_carga
                        );
                    END IF;

                    IF (IFNULL(OLD.alt_carga, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "alt_carga", OLD.alt_carga
                        );
                    END IF;

                    IF (IFNULL(OLD.larg_carga, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "larg_carga", OLD.larg_carga
                        );
                    END IF;

                    IF (IFNULL(OLD.comp_carga, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "comp_carga", OLD.comp_carga
                        );
                    END IF;

                    IF (IFNULL(OLD.cod_tipo_veiculo, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "cod_tipo_veiculo", OLD.cod_tipo_veiculo
                        );
                    END IF;

                    IF (IFNULL(OLD.placa_coleta, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "placa_coleta", OLD.placa_coleta
                        );
                    END IF;

                    IF (IFNULL(OLD.caract_coleta, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "caract_coleta", OLD.caract_coleta
                        );
                    END IF;

                    IF (IFNULL(OLD.obs_coleta, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value_blob
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "obs_coleta", OLD.obs_coleta
                        );
                    END IF;

                    IF (IFNULL(OLD.motor_coleta_id, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "motor_coleta_id", OLD.motor_coleta_id
                        );
                    END IF;

                    IF (IFNULL(OLD.coleta_fixa, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "coleta_fixa", OLD.coleta_fixa
                        );
                    END IF;

                    IF (IFNULL(OLD.dt_efet_coleta, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "dt_efet_coleta", OLD.dt_efet_coleta
                        );
                    END IF;

                    IF (IFNULL(OLD.hr_cheg_coleta, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "hr_cheg_coleta", OLD.hr_cheg_coleta
                        );
                    END IF;

                    IF (IFNULL(OLD.hr_atend_coleta, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "hr_atend_coleta", OLD.hr_atend_coleta
                        );
                    END IF;

                    IF (IFNULL(OLD.hr_sai_coleta, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "hr_sai_coleta", OLD.hr_sai_coleta
                        );
                    END IF;

                    IF (IFNULL(OLD.cod_tipo_veiculo_nec, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "cod_tipo_veiculo_nec", OLD.cod_tipo_veiculo_nec
                        );
                    END IF;

                    IF (IFNULL(OLD.placa_entrega, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "placa_entrega", OLD.placa_entrega
                        );
                    END IF;

                    IF (IFNULL(OLD.motor_entrega_id, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "motor_entrega_id", OLD.motor_entrega_id
                        );
                    END IF;

                    IF (IFNULL(OLD.dt_efet_entrega, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "dt_efet_entrega", OLD.dt_efet_entrega
                        );
                    END IF;

                    IF (IFNULL(OLD.hr_cheg_entrega, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "hr_cheg_entrega", OLD.hr_cheg_entrega
                        );
                    END IF;

                    IF (IFNULL(OLD.hr_atend_entrega, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "hr_atend_entrega", OLD.hr_atend_entrega
                        );
                    END IF;

                    IF (IFNULL(OLD.hr_sai_entrega, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "hr_sai_entrega", OLD.hr_sai_entrega
                        );
                    END IF;

                    IF (IFNULL(OLD.recebedor, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "recebedor", OLD.recebedor
                        );
                    END IF;

                    IF (IFNULL(OLD.receber_nf_frete, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "receber_nf_frete", OLD.receber_nf_frete
                        );
                    END IF;

                    IF (IFNULL(OLD.sit_nf_frete, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "sit_nf_frete", OLD.sit_nf_frete
                        );
                    END IF;

                    IF (IFNULL(OLD.distancia_km, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "distancia_km", OLD.distancia_km
                        );
                    END IF;

                    IF (IFNULL(OLD.tempo_estimado, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "tempo_estimado", OLD.tempo_estimado
                        );
                    END IF;

                    IF (IFNULL(OLD.dur_prev_coleta, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "dur_prev_coleta", OLD.dur_prev_coleta
                        );
                    END IF;

                    IF (IFNULL(OLD.dur_prev_entrega, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "dur_prev_entrega", OLD.dur_prev_entrega
                        );
                    END IF;

                    IF (IFNULL(OLD.instrucao, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "instrucao", OLD.instrucao
                        );
                    END IF;

                    IF (IFNULL(OLD.txt_instrucao, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "txt_instrucao", OLD.txt_instrucao
                        );
                    END IF;                    

                    IF (IFNULL(OLD.placa_baldeacao, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "placa_baldeacao", OLD.placa_baldeacao
                        );
                    END IF;

                    IF (IFNULL(OLD.baldeada, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "baldeada", OLD.baldeada
                        );
                    END IF;

                    IF (IFNULL(OLD.status, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "status", OLD.status
                        );
                    END IF;

                    IF (IFNULL(OLD.mot_nao_coleta, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "mot_nao_coleta", OLD.mot_nao_coleta
                        );
                    END IF;

                    IF (IFNULL(OLD.obs_nao_coleta, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "obs_nao_coleta", OLD.obs_nao_coleta
                        );
                    END IF;

                    IF (IFNULL(OLD.solic_origem_id, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "solic_origem_id", OLD.solic_origem_id
                        );
                    END IF;

                    IF (IFNULL(OLD.origem_reg, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "origem_reg", OLD.origem_reg
                        );
                    END IF;

                    IF (IFNULL(OLD.coleta_export, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "coleta_export", OLD.coleta_export
                        );
                    END IF;

                    IF (IFNULL(OLD.dt_coleta_export, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "dt_coleta_export", OLD.dt_coleta_export
                        );
                    END IF;

                    IF (IFNULL(OLD.entrega_export, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "entrega_export", OLD.entrega_export
                        );
                    END IF;

                    IF (IFNULL(OLD.dt_entrega_export, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta", "delete", "id", OLD.id, @client_addr, "dt_entrega_export", OLD.dt_entrega_export
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_coleta_gravar_log_after_delete');
    }
}
