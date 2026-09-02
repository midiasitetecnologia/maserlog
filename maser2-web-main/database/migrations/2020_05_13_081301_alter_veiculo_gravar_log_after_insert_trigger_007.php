<?php

use Illuminate\Database\Migrations\Migration;

class AlterVeiculoGravarLogAfterInsertTrigger007 extends Migration
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
		DB::unprepared('DROP TRIGGER IF EXISTS tr_veiculo_gravar_log_after_insert');
		DB::unprepared('
            CREATE TRIGGER tr_veiculo_gravar_log_after_insert AFTER INSERT ON veiculo FOR EACH ROW
                BEGIN                                    

                    -- As variaveis @uuid, @date_time serve como identificação para saber oque foi alterado nesse lote de dados.
                    SET @uuid = UUID();                    
                    SET @date_time = current_timestamp(6);
                    SET @client_addr = get_client_addr();                  

                    IF (IFNULL(NEW.cod_tipo_veiculo, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "veiculo", "insert", "placa", NEW.placa, @client_addr, "cod_tipo_veiculo", 
                          NEW.cod_tipo_veiculo
                        ); 
                    END IF;

                    IF (IFNULL(NEW.milk_run, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "veiculo", "insert", "placa", NEW.placa, @client_addr, "milk_run", 
                          NEW.milk_run
                        ); 
					END IF;
					
					IF (IFNULL(NEW.sis_carga_empilha, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "veiculo", "insert", "placa", NEW.placa, @client_addr, "sis_carga_empilha", 
                          NEW.sis_carga_empilha
                        ); 
					END IF;
					
					IF (IFNULL(NEW.sis_carga_ponte, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "veiculo", "insert", "placa", NEW.placa, @client_addr, "sis_carga_ponte", 
                          NEW.sis_carga_ponte
                        ); 
					END IF;
					
					IF (IFNULL(NEW.sis_carga_manual, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "veiculo", "insert", "placa", NEW.placa, @client_addr, "sis_carga_manual", 
                          NEW.sis_carga_manual
                        ); 
                    END IF;

                    IF (IFNULL(NEW.largura, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "veiculo", "insert", "placa", NEW.placa, @client_addr, "largura", 
                          NEW.largura
                        ); 
                    END IF;
                    
                    IF (IFNULL(NEW.comprimento, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "veiculo", "insert", "placa", NEW.placa, @client_addr, "comprimento", 
                          NEW.comprimento
                        ); 
                    END IF;

                    IF (IFNULL(NEW.altura, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "veiculo", "insert", "placa", NEW.placa, @client_addr, "altura", 
                          NEW.altura
                        ); 
                    END IF;

                    IF (IFNULL(NEW.cap_cub, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "veiculo", "insert", "placa", NEW.placa, @client_addr, "cap_cub", 
                          NEW.cap_cub
                        ); 
                    END IF;

                    IF (IFNULL(NEW.cap_kg, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "veiculo", "insert", "placa", NEW.placa, @client_addr, "cap_kg", 
                          NEW.cap_kg
                        ); 
                    END IF;

                    IF (IFNULL(NEW.nivel_cons, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "veiculo", "insert", "placa", NEW.placa, @client_addr, "nivel_cons", 
                          NEW.nivel_cons
                        ); 
                    END IF;

                    IF (IFNULL(NEW.motorista_id, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "veiculo", "insert", "placa", NEW.placa, @client_addr, "motorista_id", 
                          NEW.motorista_id
                        ); 
                    END IF;

                    IF (IFNULL(NEW.ativo, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "veiculo", "insert", "placa", NEW.placa, @client_addr, "ativo", 
                          NEW.ativo
                        ); 
                    END IF;

                    IF (IFNULL(NEW.usar_gps, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "veiculo", "insert", "placa", NEW.placa, @client_addr, "usar_gps", 
                          NEW.usar_gps
                        ); 
					END IF;
					
					IF (IFNULL(NEW.placa_cavalo, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "veiculo", "insert", "placa", NEW.placa, @client_addr, "placa_cavalo", 
                          NEW.placa_cavalo
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
		DB::unprepared('DROP TRIGGER IF EXISTS tr_veiculo_gravar_log_after_insert');
	}
}
