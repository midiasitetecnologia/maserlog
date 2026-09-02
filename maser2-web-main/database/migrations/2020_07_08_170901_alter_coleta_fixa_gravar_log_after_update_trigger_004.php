<?php

use Illuminate\Database\Migrations\Migration;

class AlterColetaFixaGravarLogAfterUpdateTrigger004 extends Migration
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_coleta_fixa_gravar_log_after_update');
        DB::unprepared('
            CREATE TRIGGER tr_coleta_fixa_gravar_log_after_update AFTER UPDATE ON coleta_fixa FOR EACH ROW
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
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_fixa", "update", "id", NEW.id, @client_addr, "empresa", OLD.empresa, 
                          NEW.empresa
                        );                    
                    END IF;

                    IF (IFNULL(OLD.cod_cliente, "") != IFNULL(NEW.cod_cliente, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_fixa", "update", "id", NEW.id, @client_addr, "cod_cliente", OLD.cod_cliente, 
                          NEW.cod_cliente
                        );
                    END IF;                    

                    IF (IFNULL(OLD.tipo_coleta, "") != IFNULL(NEW.tipo_coleta, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_fixa", "update", "id", NEW.id, @client_addr, "tipo_coleta", 
                          OLD.tipo_coleta, NEW.tipo_coleta
                        ); 
                    END IF;

                    IF (IFNULL(OLD.dt_ini, "") != IFNULL(NEW.dt_ini, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_fixa", "update", "id", NEW.id, @client_addr, "dt_ini", 
                          OLD.dt_ini, NEW.dt_ini
                        ); 
                    END IF;
                    
                    IF (IFNULL(OLD.dt_fim, "") != IFNULL(NEW.dt_fim, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_fixa", "update", "id", NEW.id, @client_addr, "dt_fim", 
                          OLD.dt_fim, NEW.dt_fim
                        ); 
                    END IF;

                    IF (IFNULL(OLD.cod_loc_coleta, "") != IFNULL(NEW.cod_loc_coleta, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_fixa", "update", "id", NEW.id, @client_addr, "cod_loc_coleta", 
                          OLD.cod_loc_coleta, NEW.cod_loc_coleta
                        ); 
                    END IF;

                    IF (IFNULL(OLD.cod_loc_entrega, "") != IFNULL(NEW.cod_loc_entrega, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_fixa", "update", "id", NEW.id, @client_addr, "cod_loc_entrega", 
                          OLD.cod_loc_entrega, NEW.cod_loc_entrega
                        ); 
                    END IF;

                    IF (IFNULL(OLD.cod_tipo_veiculo, "") != IFNULL(NEW.cod_tipo_veiculo, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_fixa", "update", "id", NEW.id, @client_addr, "cod_tipo_veiculo", 
                          OLD.cod_tipo_veiculo, NEW.cod_tipo_veiculo
                        ); 
					END IF;

					IF (IFNULL(OLD.sis_carga, "") != IFNULL(NEW.sis_carga, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_fixa", "update", "id", NEW.id, @client_addr, "sis_carga", 
                          OLD.sis_carga, NEW.sis_carga
                        ); 
					END IF;
					
					IF (IFNULL(OLD.receber_nf_frete, "") != IFNULL(NEW.receber_nf_frete, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_fixa", "update", "id", NEW.id, @client_addr, "receber_nf_frete", 
                          OLD.receber_nf_frete, NEW.receber_nf_frete
                        ); 
					END IF;
					
					IF (IFNULL(OLD.aceitar_foto_rom, "") != IFNULL(NEW.aceitar_foto_rom, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_fixa", "update", "id", NEW.id, @client_addr, "aceitar_foto_rom", 
                          OLD.aceitar_foto_rom, NEW.aceitar_foto_rom
                        ); 
                    END IF;

                    IF (IFNULL(OLD.placa_coleta, "") != IFNULL(NEW.placa_coleta, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_fixa", "update", "id", NEW.id, @client_addr, "placa_coleta", 
                          OLD.placa_coleta, NEW.placa_coleta
                        ); 
                    END IF;

                    IF (IFNULL(OLD.segunda, "") != IFNULL(NEW.segunda, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_fixa", "update", "id", NEW.id, @client_addr, "segunda", 
                          OLD.segunda, NEW.segunda
                        ); 
                    END IF;

                    IF (IFNULL(OLD.terca, "") != IFNULL(NEW.terca, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_fixa", "update", "id", NEW.id, @client_addr, "terca", 
                          OLD.terca, NEW.terca
                        ); 
                    END IF;

                    IF (IFNULL(OLD.quarta, "") != IFNULL(NEW.quarta, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_fixa", "update", "id", NEW.id, @client_addr, "quarta", 
                          OLD.quarta, NEW.quarta
                        ); 
                    END IF;

                    IF (IFNULL(OLD.quinta, "") != IFNULL(NEW.quinta, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_fixa", "update", "id", NEW.id, @client_addr, "quinta", 
                          OLD.quinta, NEW.quinta
                        ); 
                    END IF;

                    IF (IFNULL(OLD.sexta, "") != IFNULL(NEW.sexta, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_fixa", "update", "id", NEW.id, @client_addr, "sexta", 
                          OLD.sexta, NEW.sexta
                        ); 
                    END IF;

                    IF (IFNULL(OLD.sabado, "") != IFNULL(NEW.sabado, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_fixa", "update", "id", NEW.id, @client_addr, "sabado", 
                          OLD.sabado, NEW.sabado
                        ); 
                    END IF;

                    IF (IFNULL(OLD.hr_prev_coleta, "") != IFNULL(NEW.hr_prev_coleta, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_fixa", "update", "id", NEW.id, @client_addr, "hr_prev_coleta", 
                          OLD.hr_prev_coleta, NEW.hr_prev_coleta
                        ); 
                    END IF;

                    IF (IFNULL(OLD.hr_prev_entrega, "") != IFNULL(NEW.hr_prev_entrega, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_fixa", "update", "id", NEW.id, @client_addr, "hr_prev_entrega", 
                          OLD.hr_prev_entrega, NEW.hr_prev_entrega
                        ); 
                    END IF;

                    IF (IFNULL(OLD.dois_turnos, "") != IFNULL(NEW.dois_turnos, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_fixa", "update", "id", NEW.id, @client_addr, "dois_turnos", 
                          OLD.dois_turnos, NEW.dois_turnos
                        ); 
                    END IF;

                    IF (IFNULL(OLD.t1_hora_ini, "") != IFNULL(NEW.t1_hora_ini, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_fixa", "update", "id", NEW.id, @client_addr, "t1_hora_ini", 
                          OLD.t1_hora_ini, NEW.t1_hora_ini
                        ); 
                    END IF;

                    IF (IFNULL(OLD.t1_hora_fim, "") != IFNULL(NEW.t1_hora_fim, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_fixa", "update", "id", NEW.id, @client_addr, "t1_hora_fim", 
                          OLD.t1_hora_fim, NEW.t1_hora_fim
                        ); 
                    END IF;

                    IF (IFNULL(OLD.t2_hora_ini, "") != IFNULL(NEW.t2_hora_ini, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_fixa", "update", "id", NEW.id, @client_addr, "t2_hora_ini", 
                          OLD.t2_hora_ini, NEW.t2_hora_ini
                        ); 
                    END IF;

                    IF (IFNULL(OLD.t2_hora_fim, "") != IFNULL(NEW.t2_hora_fim, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_fixa", "update", "id", NEW.id, @client_addr, "t2_hora_fim", 
                          OLD.t2_hora_fim, NEW.t2_hora_fim
                        ); 
                    END IF;

                    IF (IFNULL(OLD.cont_cancel, "") != IFNULL(NEW.cont_cancel, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_fixa", "update", "id", NEW.id, @client_addr, "cont_cancel", 
                          OLD.cont_cancel, NEW.cont_cancel
                        ); 
                    END IF;

                    IF (IFNULL(OLD.dt_cancel, "") != IFNULL(NEW.dt_cancel, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_fixa", "update", "id", NEW.id, @client_addr, "dt_cancel", 
                          OLD.dt_cancel, NEW.dt_cancel
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_coleta_fixa_gravar_log_after_update');
    }
}
