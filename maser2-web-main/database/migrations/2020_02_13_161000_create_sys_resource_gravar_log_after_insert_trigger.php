<?php

use Illuminate\Database\Migrations\Migration;

class CreateSysResourceGravarLogAfterInsertTrigger extends Migration
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_sys_resource_gravar_log_after_insert');
        DB::unprepared('
            CREATE TRIGGER tr_sys_resource_gravar_log_after_insert AFTER INSERT ON sys_resource FOR EACH ROW
                BEGIN                                    

                    -- As variaveis @uuid, @date_time serve como identificação para saber oque foi alterado nesse lote de dados.
                    SET @uuid = UUID();                    
                    SET @date_time = current_timestamp(6);
                    SET @client_addr = get_client_addr();

                    IF (IFNULL(NEW.name, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "sys_resource", "insert", "id", NEW.id, @client_addr, "name", NEW.name
                        );                    
                    END IF;

                    IF (IFNULL(NEW.label, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "sys_resource", "insert", "id", NEW.id, @client_addr, "label", NEW.label
                        );                    
                    END IF;

                    IF (IFNULL(NEW.active, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "sys_resource", "insert", "id", NEW.id, @client_addr, "active", NEW.active
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_sys_resource_gravar_log_after_insert');
    }
}
