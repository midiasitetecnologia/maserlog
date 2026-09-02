<?php

use Illuminate\Database\Migrations\Migration;

class CreateMotoristaGravarLogAfterInsertTrigger extends Migration
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
		DB::unprepared('DROP TRIGGER IF EXISTS tr_motorista_gravar_log_after_insert');
		DB::unprepared('
            CREATE TRIGGER tr_motorista_gravar_log_after_insert AFTER INSERT ON motorista FOR EACH ROW
                BEGIN                                    

                    -- As variaveis @uuid, @date_time serve como identificação para saber oque foi alterado nesse lote de dados.
                    SET @uuid = UUID();                    
                    SET @date_time = current_timestamp(6);
                    SET @client_addr = get_client_addr();

                    IF (IFNULL(NEW.cpf, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "motorista", "insert", "id", NEW.id, @client_addr, "cpf", NEW.cpf
                        );                    
                    END IF;
                    
                    IF (IFNULL(NEW.nome, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "motorista", "insert", "id", NEW.id, @client_addr, "nome", NEW.nome
                        );
                    END IF;                    

                    IF (IFNULL(NEW.celular, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "motorista", "insert", "id", NEW.id, @client_addr, "celular", 
                          NEW.celular
                        ); 
                    END IF;
                    
                    IF (IFNULL(NEW.user_id, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "motorista", "insert", "id", NEW.id, @client_addr, "user_id", 
                          NEW.user_id
                        ); 
                    END IF;

                    IF (IFNULL(NEW.ativo, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "motorista", "insert", "id", NEW.id, @client_addr, "ativo", 
                          NEW.ativo
                        ); 
                    END IF;

                    IF (IFNULL(NEW.id_disp, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "motorista", "insert", "id", NEW.id, @client_addr, "id_disp", 
                          NEW.id_disp
                        ); 
                    END IF;

                    IF (IFNULL(NEW.logado, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "motorista", "insert", "id", NEW.id, @client_addr, "logado", 
                          NEW.logado
                        ); 
                    END IF;

                    IF (IFNULL(NEW.dt_logado, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "motorista", "insert", "id", NEW.id, @client_addr, "dt_logado", 
                          NEW.dt_logado
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
		DB::unprepared('DROP TRIGGER IF EXISTS tr_motorista_gravar_log_after_insert');
	}
}
