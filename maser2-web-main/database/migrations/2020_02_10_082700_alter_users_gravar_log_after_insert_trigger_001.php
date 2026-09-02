<?php

use Illuminate\Database\Migrations\Migration;

class AlterUsersGravarLogAfterInsertTrigger001 extends Migration
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_users_gravar_log_after_insert');
        DB::unprepared('
            CREATE TRIGGER tr_users_gravar_log_after_insert AFTER INSERT ON users FOR EACH ROW
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
                        ( @uuid, NEW.ass_user_id, @date_time, "users", "insert", "id", NEW.id, @client_addr, "name", NEW.name
                        );                    
                    END IF;
                    
                    IF (IFNULL(NEW.email, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "users", "insert", "id", NEW.id, @client_addr, "email", NEW.email
                        );
                    END IF;                    

                    IF (IFNULL(NEW.email_verified_at, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "users", "insert", "id", NEW.id, @client_addr, "email_verified_at", 
                          NEW.email_verified_at
                        ); 
                    END IF;

                    IF (IFNULL(NEW.password, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "users", "insert", "id", NEW.id, @client_addr, "password", 
                          NEW.password
                        ); 
                    END IF;

                    IF (IFNULL(NEW.api_token, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "users", "insert", "id", NEW.id, @client_addr, "api_token", 
                          NEW.api_token
                        ); 
                    END IF;

                    IF (IFNULL(NEW.remember_token, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "users", "insert", "id", NEW.id, @client_addr, "remember_token", 
                          NEW.remember_token
                        ); 
                    END IF;
                    
                    IF (IFNULL(NEW.active, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "users", "insert", "id", NEW.id, @client_addr, "active", 
                          NEW.active
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_users_gravar_log_after_insert');
    }
}
