<?php

use Illuminate\Database\Migrations\Migration;

class CreateMotoristaGravarLogAfterDeleteTrigger extends Migration
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_motorista_gravar_log_after_delete');
        DB::unprepared('
            CREATE TRIGGER tr_motorista_gravar_log_after_delete AFTER DELETE ON motorista FOR EACH ROW
                BEGIN                
                    
                    -- As variaveis @uuid, @date_time serve como identificação para saber oque foi alterado nesse lote de dados.
                    SET @uuid = UUID(); 
                    SET @date_time = current_timestamp(6);
                    SET @client_addr = get_client_addr();

                    IF (IFNULL(OLD.cpf, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "motorista", "delete", "id", OLD.id, @client_addr, "cpf", OLD.cpf
                        );
                    END IF;                    

                    IF (IFNULL(OLD.nome, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "motorista", "delete", "id", OLD.id, @client_addr, "nome", 
                          OLD.nome
                        ); 
                    END IF;                   

                    IF (IFNULL(OLD.celular, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "motorista", "delete", "id", OLD.id, @client_addr, "celular", OLD.celular
                        ); 
                    END IF;

                    IF (IFNULL(OLD.user_id, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "motorista", "delete", "id", OLD.id, @client_addr, "user_id", OLD.user_id
                        ); 
                    END IF;

                    IF (IFNULL(OLD.ativo, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "motorista", "delete", "id", OLD.id, @client_addr, "ativo", OLD.ativo
                        ); 
                    END IF;

                    IF (IFNULL(OLD.id_disp, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "motorista", "delete", "id", OLD.id, @client_addr, "id_disp", OLD.id_disp
                        ); 
                    END IF;

                    IF (IFNULL(OLD.logado, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "motorista", "delete", "id", OLD.id, @client_addr, "logado", OLD.logado
                        ); 
                    END IF;

                    IF (IFNULL(OLD.dt_logado, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "motorista", "delete", "id", OLD.id, @client_addr, "dt_logado", OLD.dt_logado
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_motorista_gravar_log_after_delete');
    }
}
