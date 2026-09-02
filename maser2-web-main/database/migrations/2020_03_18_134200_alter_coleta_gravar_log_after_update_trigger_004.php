<?php

use Illuminate\Database\Migrations\Migration;

class AlterColetaGravarLogAfterUpdateTrigger004 extends Migration
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_coleta_gravar_log_after_update');
        DB::unprepared('
            CREATE TRIGGER tr_coleta_gravar_log_after_update AFTER UPDATE ON coleta FOR EACH ROW
                BEGIN
                    
                    -- As variaveis @uuid, @date_time serve como identificação para saber oque foi alterado nesse lote de dados.
                    SET @uuid = UUID(); 
                    SET @date_time = current_timestamp(6);
                    SET @client_addr = get_client_addr();

                    IF (IFNULL(OLD.empresa, "") != IFNULL(NEW.empresa, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "empresa", OLD.empresa, 
                          NEW.empresa
                        );                    
                    END IF;

                    IF (IFNULL(OLD.numero, "") != IFNULL(NEW.numero, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "numero", OLD.numero, 
                          NEW.numero
                        );                    
                    END IF;                    

                    IF (IFNULL(OLD.data_cad, "") != IFNULL(NEW.data_cad, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "data_cad", OLD.data_cad, 
                          NEW.data_cad
                        );                    
                    END IF;                    

                    IF (IFNULL(OLD.hora_cad, "") != IFNULL(NEW.hora_cad, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "hora_cad", OLD.hora_cad, 
                          NEW.hora_cad
                        );                    
                    END IF;

                    IF (IFNULL(OLD.cod_cliente, "") != IFNULL(NEW.cod_cliente, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "cod_cliente", OLD.cod_cliente, 
                          NEW.cod_cliente
                        );                    
                    END IF;                    

                    IF (IFNULL(OLD.dt_prev_coleta, "") != IFNULL(NEW.dt_prev_coleta, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "dt_prev_coleta", OLD.dt_prev_coleta, 
                          NEW.dt_prev_coleta
                        );                    
                    END IF;

                    IF (IFNULL(OLD.hr_prev_coleta, "") != IFNULL(NEW.hr_prev_coleta, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "hr_prev_coleta", OLD.hr_prev_coleta, 
                          NEW.hr_prev_coleta
                        );                    
                    END IF;

                    IF (IFNULL(OLD.dt_prev_entrega, "") != IFNULL(NEW.dt_prev_entrega, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "dt_prev_entrega", OLD.dt_prev_entrega, 
                          NEW.dt_prev_entrega
                        );                    
                    END IF;                    

                    IF (IFNULL(OLD.hr_prev_entrega, "") != IFNULL(NEW.hr_prev_entrega, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "hr_prev_entrega", OLD.hr_prev_entrega, 
                          NEW.hr_prev_entrega
                        );                    
                    END IF;                    

                    IF (IFNULL(OLD.entrega_urgente, "") != IFNULL(NEW.entrega_urgente, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "entrega_urgente", OLD.entrega_urgente, 
                          NEW.entrega_urgente
                        );                    
                    END IF;

                    IF (IFNULL(OLD.cod_loc_coleta, "") != IFNULL(NEW.cod_loc_coleta, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "cod_loc_coleta", OLD.cod_loc_coleta, 
                          NEW.cod_loc_coleta
                        );                    
                    END IF;                    

                    IF (IFNULL(OLD.cod_loc_entrega, "") != IFNULL(NEW.cod_loc_entrega, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "cod_loc_entrega", OLD.cod_loc_entrega, 
                          NEW.cod_loc_entrega
                        );                    
                    END IF;

                    IF (IFNULL(OLD.peso, "") != IFNULL(NEW.peso, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "peso", OLD.peso, 
                          NEW.peso
                        );                    
                    END IF;

                    IF (IFNULL(OLD.solicitante, "") != IFNULL(NEW.solicitante, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "solicitante", OLD.solicitante, 
                          NEW.solicitante
                        );                    
                    END IF;                    

                    IF (IFNULL(OLD.volumes, "") != IFNULL(NEW.volumes, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "volumes", OLD.volumes, 
                          NEW.volumes
                        );                    
                    END IF;                    

                    IF (IFNULL(OLD.especie, "") != IFNULL(NEW.especie, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "especie", OLD.especie, 
                          NEW.especie
                        );                    
                    END IF;

                    IF (IFNULL(OLD.sis_carga, "") != IFNULL(NEW.sis_carga, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "sis_carga", OLD.sis_carga, 
                          NEW.sis_carga
                        );                    
                    END IF;                    

                    IF (IFNULL(OLD.alt_carga, "") != IFNULL(NEW.alt_carga, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "alt_carga", OLD.alt_carga, 
                          NEW.alt_carga
                        );                    
                    END IF;

                    IF (IFNULL(OLD.larg_carga, "") != IFNULL(NEW.larg_carga, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "larg_carga", OLD.larg_carga, 
                          NEW.larg_carga
                        );                    
                    END IF;

                    IF (IFNULL(OLD.comp_carga, "") != IFNULL(NEW.comp_carga, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "comp_carga", OLD.comp_carga, 
                          NEW.comp_carga
                        );                    
                    END IF;                    

                    IF (IFNULL(OLD.cod_tipo_veiculo, "") != IFNULL(NEW.cod_tipo_veiculo, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "cod_tipo_veiculo", OLD.cod_tipo_veiculo, 
                          NEW.cod_tipo_veiculo
                        );                    
                    END IF;                    

                    IF (IFNULL(OLD.placa_coleta, "") != IFNULL(NEW.placa_coleta, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "placa_coleta", OLD.placa_coleta, 
                          NEW.placa_coleta
                        );                    
                    END IF;

                    IF (IFNULL(OLD.caract_coleta, "") != IFNULL(NEW.caract_coleta, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "caract_coleta", OLD.caract_coleta, 
                          NEW.caract_coleta
                        );                    
                    END IF;
                    
                    IF (IFNULL(OLD.obs_coleta, "") != IFNULL(NEW.obs_coleta, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value_blob, new_value_blob
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "obs_coleta", OLD.obs_coleta, 
                          NEW.obs_coleta
                        );                    
                    END IF;

                    IF (IFNULL(OLD.motor_coleta_id, "") != IFNULL(NEW.motor_coleta_id, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "motor_coleta_id", OLD.motor_coleta_id, 
                          NEW.motor_coleta_id
                        );                    
                    END IF;

                    IF (IFNULL(OLD.coleta_fixa, "") != IFNULL(NEW.coleta_fixa, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "coleta_fixa", OLD.coleta_fixa, 
                          NEW.coleta_fixa
                        );                    
                    END IF;

                    IF (IFNULL(OLD.dt_efet_coleta, "") != IFNULL(NEW.dt_efet_coleta, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "dt_efet_coleta", OLD.dt_efet_coleta, 
                          NEW.dt_efet_coleta
                        );                    
                    END IF;                    

                    IF (IFNULL(OLD.hr_cheg_coleta, "") != IFNULL(NEW.hr_cheg_coleta, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "hr_cheg_coleta", OLD.hr_cheg_coleta, 
                          NEW.hr_cheg_coleta
                        );                    
                    END IF;                    

                    IF (IFNULL(OLD.hr_atend_coleta, "") != IFNULL(NEW.hr_atend_coleta, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "hr_atend_coleta", OLD.hr_atend_coleta, 
                          NEW.hr_atend_coleta
                        );                    
                    END IF;

                    IF (IFNULL(OLD.hr_sai_coleta, "") != IFNULL(NEW.hr_sai_coleta, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "hr_sai_coleta", OLD.hr_sai_coleta, 
                          NEW.hr_sai_coleta
                        );                    
                    END IF;                    

                    IF (IFNULL(OLD.placa_entrega, "") != IFNULL(NEW.placa_entrega, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "placa_entrega", OLD.placa_entrega, 
                          NEW.placa_entrega
                        );                    
                    END IF;

                    IF (IFNULL(OLD.motor_entrega_id, "") != IFNULL(NEW.motor_entrega_id, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "motor_entrega_id", OLD.motor_entrega_id, 
                          NEW.motor_entrega_id
                        );                    
                    END IF;

                    IF (IFNULL(OLD.dt_efet_entrega, "") != IFNULL(NEW.dt_efet_entrega, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "dt_efet_entrega", OLD.dt_efet_entrega, 
                          NEW.dt_efet_entrega
                        );                    
                    END IF;                    

                    IF (IFNULL(OLD.hr_cheg_entrega, "") != IFNULL(NEW.hr_cheg_entrega, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "hr_cheg_entrega", OLD.hr_cheg_entrega, 
                          NEW.hr_cheg_entrega
                        );                    
                    END IF;                    

                    IF (IFNULL(OLD.hr_atend_entrega, "") != IFNULL(NEW.hr_atend_entrega, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "hr_atend_entrega", OLD.hr_atend_entrega, 
                          NEW.hr_atend_entrega
                        );                    
                    END IF;

                    IF (IFNULL(OLD.hr_sai_entrega, "") != IFNULL(NEW.hr_sai_entrega, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "hr_sai_entrega", OLD.hr_sai_entrega, 
                          NEW.hr_sai_entrega
                        );                    
                    END IF;                    

                    IF (IFNULL(OLD.recebedor, "") != IFNULL(NEW.recebedor, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "recebedor", OLD.recebedor, 
                          NEW.recebedor
                        );                    
                    END IF;

                    IF (IFNULL(OLD.receber_nf_frete, "") != IFNULL(NEW.receber_nf_frete, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "receber_nf_freteta_id", OLD.receber_nf_frete, 
                          NEW.receber_nf_frete
                        );                    
					END IF;
					
					IF (IFNULL(OLD.dt_desloca_coleta, "") != IFNULL(NEW.dt_desloca_coleta, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "dt_desloca_coleta", OLD.dt_desloca_coleta, 
                          NEW.dt_desloca_coleta
                        );                    
                    END IF;                    
					
					IF (IFNULL(OLD.dt_desloca_entrega, "") != IFNULL(NEW.dt_desloca_entrega, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "dt_desloca_entrega", OLD.dt_desloca_entrega, 
                          NEW.dt_desloca_entrega
                        );                    
					END IF;					

                    IF (IFNULL(OLD.distancia_km, "") != IFNULL(NEW.distancia_km, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "distancia_km", OLD.distancia_km, 
                          NEW.distancia_km
                        );                    
                    END IF;                    

                    IF (IFNULL(OLD.tempo_estimado, "") != IFNULL(NEW.tempo_estimado, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "tempo_estimado", OLD.tempo_estimado, 
                          NEW.tempo_estimado
                        );                    
                    END IF;                    

                    IF (IFNULL(OLD.dur_prev_coleta, "") != IFNULL(NEW.dur_prev_coleta, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "dur_prev_coleta", OLD.dur_prev_coleta, 
                          NEW.dur_prev_coleta
                        );                    
                    END IF;

                    IF (IFNULL(OLD.dur_prev_entrega, "") != IFNULL(NEW.dur_prev_entrega, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "dur_prev_entrega", OLD.dur_prev_entrega, 
                          NEW.dur_prev_entrega
                        );                    
                    END IF;                    

                    IF (IFNULL(OLD.instrucao, "") != IFNULL(NEW.instrucao, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "instrucao", OLD.instrucao, 
                          NEW.instrucao
                        );                    
                    END IF;

                    IF (IFNULL(OLD.txt_instrucao, "") != IFNULL(NEW.txt_instrucao, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "txt_instrucao", OLD.txt_instrucao, 
                          NEW.txt_instrucao
                        );                    
                    END IF;

                    IF (IFNULL(OLD.descarga_coleta, "") != IFNULL(NEW.descarga_coleta, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "descarga_coleta", OLD.descarga_coleta, 
                          NEW.descarga_coleta
                        );                    
                    END IF;                    

                    IF (IFNULL(OLD.dt_descarga_coleta, "") != IFNULL(NEW.dt_descarga_coleta, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "dt_descarga_coleta", OLD.dt_descarga_coleta, 
                          NEW.dt_descarga_coleta
                        );                    
                    END IF;                    

                    IF (IFNULL(OLD.placa_baldeacao, "") != IFNULL(NEW.placa_baldeacao, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "placa_baldeacao", OLD.placa_baldeacao, 
                          NEW.placa_baldeacao
                        );                    
                    END IF;

                    IF (IFNULL(OLD.status, "") != IFNULL(NEW.status, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "status", OLD.status, 
                          NEW.status
                        );                    
                    END IF;                    

                    IF (IFNULL(OLD.mot_nao_coleta, "") != IFNULL(NEW.mot_nao_coleta, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "mot_nao_coleta", OLD.mot_nao_coleta, 
                          NEW.mot_nao_coleta
                        );                    
                    END IF;

                    IF (IFNULL(OLD.obs_nao_coleta, "") != IFNULL(NEW.obs_nao_coleta, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "obs_nao_coleta", OLD.obs_nao_coleta, 
                          NEW.obs_nao_coleta
                        );                    
                    END IF;

                    IF (IFNULL(OLD.solic_origem_id, "") != IFNULL(NEW.solic_origem_id, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "solic_origem_id", OLD.solic_origem_id, 
                          NEW.solic_origem_id
                        );                    
                    END IF;

                    IF (IFNULL(OLD.origem_reg, "") != IFNULL(NEW.origem_reg, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "origem_reg", OLD.origem_reg, 
                          NEW.origem_reg
                        );                    
                    END IF;
                    
                    IF (IFNULL(OLD.coleta_export, "") != IFNULL(NEW.coleta_export, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "coleta_export", OLD.coleta_export, 
                          NEW.coleta_export
                        );                    
                    END IF;
                    
                    IF (IFNULL(OLD.dt_coleta_export, "") != IFNULL(NEW.dt_coleta_export, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "dt_coleta_export", OLD.dt_coleta_export, 
                          NEW.dt_coleta_export
                        );                    
                    END IF;
                    
                    IF (IFNULL(OLD.entrega_export, "") != IFNULL(NEW.entrega_export, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "entrega_export", OLD.entrega_export, 
                          NEW.entrega_export
                        );                    
                    END IF;
                    
                    IF (IFNULL(OLD.dt_entrega_export, "") != IFNULL(NEW.dt_entrega_export, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta", "update", "id", NEW.id, @client_addr, "dt_entrega_export", OLD.dt_entrega_export, 
                          NEW.dt_entrega_export
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_coleta_gravar_log_after_update');
    }
}
