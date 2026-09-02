<?php

use Illuminate\Database\Migrations\Migration;

class CreateSysCfgGravarLogAfterDeleteTrigger extends Migration
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_sys_cfg_gravar_log_after_delete');
        DB::unprepared('
            CREATE TRIGGER tr_sys_cfg_gravar_log_after_delete AFTER DELETE ON sys_cfg FOR EACH ROW
                BEGIN                
                    
                    -- As variaveis @uuid, @date_time serve como identificação para saber oque foi alterado nesse lote de dados.
                    SET @uuid = UUID(); 
                    SET @date_time = current_timestamp(6);
                    SET @client_addr = get_client_addr();

                    IF (IFNULL(OLD.status, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "sys_cfg", "delete", "id", OLD.id, @client_addr, "status", OLD.status
                        );
                    END IF;                    

                    IF (IFNULL(OLD.dt_ini_status, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "sys_cfg", "delete", "id", OLD.id, @client_addr, "dt_ini_status", 
                          OLD.dt_ini_status
                        ); 
                    END IF;                   

                    IF (IFNULL(OLD.dt_fim_status, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "sys_cfg", "delete", "id", OLD.id, @client_addr, "dt_fim_status", OLD.dt_fim_status
                        ); 
                    END IF;                                                                                                     

                    IF (IFNULL(OLD.msg_status, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value_blob
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "sys_cfg", "delete", "id", OLD.id, @client_addr, "msg_status", OLD.msg_status
                        ); 
                    END IF;                                                                                                     

                    IF (IFNULL(OLD.url_redirect, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "sys_cfg", "delete", "id", OLD.id, @client_addr, "url_redirect", OLD.url_redirect
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_sys_cfg_gravar_log_after_delete');
    }
}
