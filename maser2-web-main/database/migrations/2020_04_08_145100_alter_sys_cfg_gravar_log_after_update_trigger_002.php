<?php

use Illuminate\Database\Migrations\Migration;

class AlterSysCfgGravarLogAfterUpdateTrigger002 extends Migration
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_sys_cfg_gravar_log_after_update');
        DB::unprepared('
            CREATE TRIGGER tr_sys_cfg_gravar_log_after_update AFTER UPDATE ON sys_cfg FOR EACH ROW
                BEGIN
                    
                    -- As variaveis @uuid, @date_time serve como identificação para saber oque foi alterado nesse lote de dados.
                    SET @uuid = UUID(); 
                    SET @date_time = current_timestamp(6);
                    SET @client_addr = get_client_addr();

                    IF (IFNULL(OLD.status, "") != IFNULL(NEW.status, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "sys_cfg", "update", "id", NEW.id, @client_addr, "status", OLD.status, 
                          NEW.status
                        );                    
                    END IF;

                    IF (IFNULL(OLD.dt_ini_status, "") != IFNULL(NEW.dt_ini_status, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "sys_cfg", "update", "id", NEW.id, @client_addr, "dt_ini_status", OLD.dt_ini_status, 
                          NEW.dt_ini_status
                        );
                    END IF;                    

                    IF (IFNULL(OLD.dt_fim_status, "") != IFNULL(NEW.dt_fim_status, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "sys_cfg", "update", "id", NEW.id, @client_addr, "dt_fim_status", 
                          OLD.dt_fim_status, NEW.dt_fim_status
                        ); 
                    END IF;

                    IF (IFNULL(OLD.msg_status, "") != IFNULL(NEW.msg_status, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value_blob, new_value_blob
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "sys_cfg", "update", "id", NEW.id, @client_addr, "msg_status", 
                          OLD.msg_status, NEW.msg_status
                        ); 
                    END IF;
                    
                    IF (IFNULL(OLD.url_redirect, "") != IFNULL(NEW.url_redirect, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "sys_cfg", "update", "id", NEW.id, @client_addr, "url_redirect", 
                          OLD.url_redirect, NEW.url_redirect
                        ); 
					END IF;

					IF (IFNULL(OLD.office_area, "") != IFNULL(NEW.office_area, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value_blob, new_value_blob
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "sys_cfg", "update", "id", NEW.id, @client_addr, "office_area", 
                          OLD.office_area, NEW.office_area
                        ); 
					END IF;
					
					IF (IFNULL(OLD.garage_area, "") != IFNULL(NEW.garage_area, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value_blob, new_value_blob
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "sys_cfg", "update", "id", NEW.id, @client_addr, "garage_area", 
                          OLD.garage_area, NEW.garage_area
                        ); 
					END IF;
					
					IF (IFNULL(OLD.pavilion_area, "") != IFNULL(NEW.pavilion_area, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value_blob, new_value_blob
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "sys_cfg", "update", "id", NEW.id, @client_addr, "pavilion_area", 
                          OLD.pavilion_area, NEW.pavilion_area
                        ); 
                    END IF;
					
					IF (IFNULL(OLD.url_sis_track, "") != IFNULL(NEW.url_sis_track, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "sys_cfg", "update", "id", NEW.id, @client_addr, "url_sis_track", 
                          OLD.url_sis_track, NEW.url_sis_track
                        ); 
					END IF;
					
					IF (IFNULL(OLD.user_sis_track, "") != IFNULL(NEW.user_sis_track, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "sys_cfg", "update", "id", NEW.id, @client_addr, "user_sis_track", 
                          OLD.user_sis_track, NEW.user_sis_track
                        ); 
					END IF;
					
					IF (IFNULL(OLD.pwd_sis_track, "") != IFNULL(NEW.pwd_sis_track, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "sys_cfg", "update", "id", NEW.id, @client_addr, "pwd_sis_track", 
                          OLD.pwd_sis_track, NEW.pwd_sis_track
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_sys_cfg_gravar_log_after_update');
    }
}
