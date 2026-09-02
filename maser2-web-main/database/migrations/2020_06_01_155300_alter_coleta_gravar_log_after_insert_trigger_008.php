<?php

use Illuminate\Database\Migrations\Migration;

class AlterColetaGravarLogAfterInsertTrigger008 extends Migration
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_coleta_gravar_log_after_insert');
        DB::unprepared('
            CREATE TRIGGER tr_coleta_gravar_log_after_insert AFTER INSERT ON coleta FOR EACH ROW
                BEGIN                                    

                    -- As variaveis @uuid, @date_time serve como identificação para saber oque foi alterado nesse lote de dados.
                    SET @uuid = UUID();                    
                    SET @date_time = current_timestamp(6);
                    SET @client_addr = get_client_addr();

                    IF (IFNULL(NEW.empresa, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "empresa", NEW.empresa
                        );                    
                    END IF;

                    IF (IFNULL(NEW.numero, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "numero", NEW.numero
                        );                    
                    END IF;

                    IF (IFNULL(NEW.data_cad, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "data_cad", NEW.data_cad
                        );                    
                    END IF;

                    IF (IFNULL(NEW.hora_cad, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "hora_cad", NEW.hora_cad
                        );                    
                    END IF;

                    IF (IFNULL(NEW.cod_cliente, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "cod_cliente", NEW.cod_cliente
                        );                    
                    END IF;

                    IF (IFNULL(NEW.dt_prev_coleta, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "dt_prev_coleta", NEW.dt_prev_coleta
                        );                    
                    END IF;

                    IF (IFNULL(NEW.hr_prev_coleta, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "hr_prev_coleta", NEW.hr_prev_coleta
                        );                    
                    END IF;

                    IF (IFNULL(NEW.dt_prev_entrega, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "dt_prev_entrega", NEW.dt_prev_entrega
                        );                    
                    END IF;

                    IF (IFNULL(NEW.hr_prev_entrega, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "hr_prev_entrega", NEW.hr_prev_entrega
                        );                    
                    END IF;

                    IF (IFNULL(NEW.entrega_urgente, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "entrega_urgente", NEW.entrega_urgente
                        );                    
                    END IF;

                    IF (IFNULL(NEW.cod_loc_coleta, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "cod_loc_coleta", NEW.cod_loc_coleta
                        );                    
                    END IF;

                    IF (IFNULL(NEW.local_coleta_cmd, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "local_coleta_cmd", NEW.local_coleta_cmd
                        );                    
                    END IF;

                    IF (IFNULL(NEW.cod_loc_entrega, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "cod_loc_entrega", NEW.cod_loc_entrega
                        );                    
                    END IF;

                    IF (IFNULL(NEW.local_entrega_cmd, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "local_entrega_cmd", NEW.local_entrega_cmd
                        );                    
                    END IF;

                    IF (IFNULL(NEW.peso, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "peso", NEW.peso
                        );                    
                    END IF;

                    IF (IFNULL(NEW.solicitante, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "solicitante", NEW.solicitante
                        );                    
                    END IF;

                    IF (IFNULL(NEW.volumes, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "volumes", NEW.volumes
                        );                    
                    END IF;

                    IF (IFNULL(NEW.especie, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "especie", NEW.especie
                        );                    
                    END IF;

                    IF (IFNULL(NEW.sis_carga, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "sis_carga", NEW.sis_carga
                        );                    
                    END IF;

                    IF (IFNULL(NEW.alt_carga, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "alt_carga", NEW.alt_carga
                        );                    
                    END IF;

                    IF (IFNULL(NEW.larg_carga, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "larg_carga", NEW.larg_carga
                        );                    
                    END IF;

                    IF (IFNULL(NEW.comp_carga, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "comp_carga", NEW.comp_carga
                        );                    
                    END IF;

                    IF (IFNULL(NEW.cod_tipo_veiculo, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "cod_tipo_veiculo", NEW.cod_tipo_veiculo
                        );                    
                    END IF;

                    IF (IFNULL(NEW.placa_coleta, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "placa_coleta", NEW.placa_coleta
                        );                    
                    END IF;

                    IF (IFNULL(NEW.caract_coleta, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "caract_coleta", NEW.caract_coleta
                        );                    
                    END IF;

                    IF (IFNULL(NEW.obs_coleta, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value_blob
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "obs_coleta", NEW.obs_coleta
                        );                    
                    END IF;

                    IF (IFNULL(NEW.motor_coleta_id, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "motor_coleta_id", NEW.motor_coleta_id
                        );                    
                    END IF;

                    IF (IFNULL(NEW.coleta_fixa, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "coleta_fixa", NEW.coleta_fixa
                        );                    
                    END IF;

                    IF (IFNULL(NEW.dt_efet_coleta, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "dt_efet_coleta", NEW.dt_efet_coleta
                        );                    
                    END IF;

                    IF (IFNULL(NEW.hr_cheg_coleta, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "hr_cheg_coleta", NEW.hr_cheg_coleta
                        );                    
                    END IF;

                    IF (IFNULL(NEW.hr_atend_coleta, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "hr_atend_coleta", NEW.hr_atend_coleta
                        );                    
                    END IF;

                    IF (IFNULL(NEW.hr_sai_coleta, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "hr_sai_coleta", NEW.hr_sai_coleta
                        );                    
                    END IF;

                    IF (IFNULL(NEW.cod_tipo_veiculo_nec, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "cod_tipo_veiculo_nec", NEW.cod_tipo_veiculo_nec
                        );                    
                    END IF;

                    IF (IFNULL(NEW.placa_entrega, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "placa_entrega", NEW.placa_entrega
                        );                    
                    END IF;

                    IF (IFNULL(NEW.motor_entrega_id, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "motor_entrega_id", NEW.motor_entrega_id
                        );                    
                    END IF;

                    IF (IFNULL(NEW.dt_efet_entrega, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "dt_efet_entrega", NEW.dt_efet_entrega
                        );                    
                    END IF;

                    IF (IFNULL(NEW.hr_cheg_entrega, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "hr_cheg_entrega", NEW.hr_cheg_entrega
                        );                    
                    END IF;

                    IF (IFNULL(NEW.hr_atend_entrega, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "hr_atend_entrega", NEW.hr_atend_entrega
                        );                    
                    END IF;

                    IF (IFNULL(NEW.hr_sai_entrega, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "hr_sai_entrega", NEW.hr_sai_entrega
                        );                    
                    END IF;

                    IF (IFNULL(NEW.recebedor, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "recebedor", NEW.recebedor
                        );                    
                    END IF;

                    IF (IFNULL(NEW.receber_nf_frete, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "receber_nf_frete", NEW.receber_nf_frete
                        );                    
                    END IF;

                    IF (IFNULL(NEW.sit_nf_frete, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "sit_nf_frete", NEW.sit_nf_frete
                        );                    
                    END IF;

                    IF (IFNULL(NEW.distancia_km, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "distancia_km", NEW.distancia_km
                        );                    
                    END IF;

                    IF (IFNULL(NEW.tempo_estimado, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "tempo_estimado", NEW.tempo_estimado
                        );                    
                    END IF;

                    IF (IFNULL(NEW.dur_prev_coleta, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "dur_prev_coleta", NEW.dur_prev_coleta
                        );                    
                    END IF;

                    IF (IFNULL(NEW.dur_prev_entrega, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "dur_prev_entrega", NEW.dur_prev_entrega
                        );                    
                    END IF;

                    IF (IFNULL(NEW.instrucao, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "instrucao", NEW.instrucao
                        );                    
                    END IF;                   

                    IF (IFNULL(NEW.txt_instrucao, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "txt_instrucao", NEW.txt_instrucao
                        );                    
                    END IF;

                    IF (IFNULL(NEW.placa_baldeacao, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "placa_baldeacao", NEW.placa_baldeacao
                        );                    
                    END IF;

                    IF (IFNULL(NEW.baldeada, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "baldeada", NEW.baldeada
                        );                    
                    END IF;

                    IF (IFNULL(NEW.status, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "status", NEW.status
                        );                    
                    END IF;                                      

                    IF (IFNULL(NEW.mot_nao_coleta, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "mot_nao_coleta", NEW.mot_nao_coleta
                        );                    
                    END IF;                   

                    IF (IFNULL(NEW.obs_nao_coleta, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "obs_nao_coleta", NEW.obs_nao_coleta
                        );                    
                    END IF;                                      

                    IF (IFNULL(NEW.solic_origem_id, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "solic_origem_id", NEW.solic_origem_id
                        );                    
                    END IF;

                    IF (IFNULL(NEW.origem_reg, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "origem_reg", NEW.origem_reg
                        );                    
                    END IF;

                    IF (IFNULL(NEW.coleta_export, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "coleta_export", NEW.coleta_export
                        );                    
                    END IF;

                    IF (IFNULL(NEW.dt_coleta_export, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "dt_coleta_export", NEW.dt_coleta_export
                        );                    
                    END IF;

                    IF (IFNULL(NEW.entrega_export, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "entrega_export", NEW.entrega_export
                        );                    
                    END IF;

                    IF (IFNULL(NEW.dt_entrega_export, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "insert", "id", NEW.id, @client_addr, "dt_entrega_export", NEW.dt_entrega_export
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_coleta_gravar_log_after_insert');
    }
}
