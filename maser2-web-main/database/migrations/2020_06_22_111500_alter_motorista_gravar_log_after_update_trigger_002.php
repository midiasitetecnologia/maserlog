<?php

use Illuminate\Database\Migrations\Migration;

class AlterMotoristaGravarLogAfterUpdateTrigger002 extends Migration
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
		DB::unprepared('DROP TRIGGER IF EXISTS tr_motorista_gravar_log_after_update');
		DB::unprepared('
            CREATE TRIGGER tr_motorista_gravar_log_after_update AFTER UPDATE ON motorista FOR EACH ROW
                BEGIN
                    
                    -- As variaveis @uuid, @date_time serve como identificação para saber oque foi alterado nesse lote de dados.
                    SET @uuid = UUID(); 
                    SET @date_time = current_timestamp(6);
                    SET @client_addr = get_client_addr();

                    IF (IFNULL(OLD.cpf, "") != IFNULL(NEW.cpf, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "motorista", "update", "id", NEW.id, @client_addr, "cpf", OLD.cpf, 
                          NEW.cpf
                        );                    
                    END IF;

                    IF (IFNULL(OLD.nome, "") != IFNULL(NEW.nome, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "motorista", "update", "id", NEW.id, @client_addr, "nome", OLD.nome, 
                          NEW.nome
                        );
                    END IF;                    

                    IF (IFNULL(OLD.celular, "") != IFNULL(NEW.celular, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "motorista", "update", "id", NEW.id, @client_addr, "celular", 
                          OLD.celular, NEW.celular
                        ); 
                    END IF;

                    IF (IFNULL(OLD.user_id, "") != IFNULL(NEW.user_id, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "motorista", "update", "id", NEW.id, @client_addr, "user_id", 
                          OLD.user_id, NEW.user_id
                        ); 
					END IF;
					
					IF (IFNULL(OLD.hr_ini_login, "") != IFNULL(NEW.hr_ini_login, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "motorista", "update", "id", NEW.id, @client_addr, "hr_ini_login", 
                          OLD.hr_ini_login, NEW.hr_ini_login
                        ); 
					END IF;
					
					IF (IFNULL(OLD.hr_fim_login, "") != IFNULL(NEW.hr_fim_login, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "motorista", "update", "id", NEW.id, @client_addr, "hr_fim_login", 
                          OLD.hr_fim_login, NEW.hr_fim_login
                        ); 
					END IF;
					
					IF (IFNULL(OLD.auto_logoff, "") != IFNULL(NEW.auto_logoff, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "motorista", "update", "id", NEW.id, @client_addr, "auto_logoff", 
                          OLD.auto_logoff, NEW.auto_logoff
                        ); 
                    END IF;

                    IF (IFNULL(OLD.ativo, "") != IFNULL(NEW.ativo, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "motorista", "update", "id", NEW.id, @client_addr, "ativo", 
                          OLD.ativo, NEW.ativo
                        ); 
                    END IF;

                    IF (IFNULL(OLD.id_disp, "") != IFNULL(NEW.id_disp, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "motorista", "update", "id", NEW.id, @client_addr, "id_disp", 
                          OLD.id_disp, NEW.id_disp
                        ); 
                    END IF;

                    IF (IFNULL(OLD.logado, "") != IFNULL(NEW.logado, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "motorista", "update", "id", NEW.id, @client_addr, "logado", 
                          OLD.logado, NEW.logado
                        ); 
                    END IF;

                    IF (IFNULL(OLD.dt_logado, "") != IFNULL(NEW.dt_logado, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "motorista", "update", "id", NEW.id, @client_addr, "dt_logado", 
                          OLD.dt_logado, NEW.dt_logado
                        ); 
					END IF;
					
					IF (IFNULL(OLD.dt_alt_cad, "") != IFNULL(NEW.dt_alt_cad, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "motorista", "update", "id", NEW.id, @client_addr, "dt_alt_cad", 
                          OLD.dt_alt_cad, NEW.dt_alt_cad
                        ); 
                    END IF;

                    IF (IFNULL(OLD.hr_alt_cad, "") != IFNULL(NEW.hr_alt_cad, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "motorista", "update", "id", NEW.id, @client_addr, "hr_alt_cad", 
                          OLD.hr_alt_cad, NEW.hr_alt_cad
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
		DB::unprepared('DROP TRIGGER IF EXISTS tr_motorista_gravar_log_after_update');
	}
}
