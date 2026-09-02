<?php

use Illuminate\Database\Migrations\Migration;

class CreateSysPermissionGravarLogAfterInsertTrigger extends Migration
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_sys_permission_gravar_log_after_insert');
        DB::unprepared('
            CREATE TRIGGER tr_sys_permission_gravar_log_after_insert AFTER INSERT ON sys_permission FOR EACH ROW
                BEGIN                                    

                    -- As variaveis @uuid, @date_time serve como identificação para saber oque foi alterado nesse lote de dados.
                    SET @uuid = UUID();                    
                    SET @date_time = current_timestamp(6);
                    SET @client_addr = get_client_addr();

                    IF (IFNULL(NEW.sys_resource_id, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "sys_permission", "insert", "id", NEW.id, @client_addr, "sys_resource_id", NEW.sys_resource_id
                        );                    
                    END IF;
                    
                    IF (IFNULL(NEW.sys_group_id, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "sys_permission", "insert", "id", NEW.id, @client_addr, "sys_group_id", NEW.sys_group_id
                        );
                    END IF;                    

                    IF (IFNULL(NEW.user_id, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "sys_permission", "insert", "id", NEW.id, @client_addr, "user_id", 
                          NEW.user_id
                        ); 
                    END IF;

                    IF (IFNULL(NEW.p_list, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "sys_permission", "insert", "id", NEW.id, @client_addr, "p_list", 
                          NEW.p_list
                        ); 
                    END IF;

                    IF (IFNULL(NEW.p_view, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "sys_permission", "insert", "id", NEW.id, @client_addr, "p_view", 
                          NEW.p_view
                        ); 
                    END IF;
                    
                    IF (IFNULL(NEW.p_create, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "sys_permission", "insert", "id", NEW.id, @client_addr, "p_create", 
                          NEW.p_create
                        ); 
                    END IF;

                    IF (IFNULL(NEW.p_update, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "sys_permission", "insert", "id", NEW.id, @client_addr, "p_update", 
                          NEW.p_update
                        ); 
                    END IF;

                    IF (IFNULL(NEW.p_delete, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "sys_permission", "insert", "id", NEW.id, @client_addr, "p_delete", 
                          NEW.p_delete
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_sys_permission_gravar_log_after_insert');
    }
}
