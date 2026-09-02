<?php

use Illuminate\Database\Migrations\Migration;

class AlterClienteGravarLogAfterInsertTrigger001 extends Migration
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
		DB::unprepared('DROP TRIGGER IF EXISTS tr_cliente_gravar_log_after_insert');
		DB::unprepared('
            CREATE TRIGGER tr_cliente_gravar_log_after_insert AFTER INSERT ON cliente FOR EACH ROW
                BEGIN                                    

                    -- As variaveis @uuid, @date_time serve como identificação para saber oque foi alterado nesse lote de dados.
                    SET @uuid = UUID();                    
                    SET @date_time = current_timestamp(6);
                    SET @client_addr = get_client_addr();

                    IF (IFNULL(NEW.empresa, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "cliente", "insert", "id", NEW.id, @client_addr, "empresa", NEW.empresa
                        );                    
                    END IF;
                    
                    IF (IFNULL(NEW.codigo, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "cliente", "insert", "id", NEW.id, @client_addr, "codigo", NEW.codigo
                        );
                    END IF;                    

                    IF (IFNULL(NEW.tipo_pessoa, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "cliente", "insert", "id", NEW.id, @client_addr, "tipo_pessoa", 
                          NEW.tipo_pessoa
                        ); 
                    END IF;

                    IF (IFNULL(NEW.nome, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "cliente", "insert", "id", NEW.id, @client_addr, "nome", 
                          NEW.nome
                        ); 
                    END IF;

                    IF (IFNULL(NEW.fantasia, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "cliente", "insert", "id", NEW.id, @client_addr, "fantasia", 
                          NEW.fantasia
                        ); 
                    END IF;
                    
                    IF (IFNULL(NEW.cpf_cnpj, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "cliente", "insert", "id", NEW.id, @client_addr, "cpf_cnpj", 
                          NEW.cpf_cnpj
                        ); 
                    END IF;

                    IF (IFNULL(NEW.fone, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "cliente", "insert", "id", NEW.id, @client_addr, "fone", 
                          NEW.fone
                        ); 
                    END IF;

                    IF (IFNULL(NEW.cep, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "cliente", "insert", "id", NEW.id, @client_addr, "cep", 
                          NEW.cep
                        ); 
                    END IF;

                    IF (IFNULL(NEW.endereco, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "cliente", "insert", "id", NEW.id, @client_addr, "endereco", 
                          NEW.endereco
                        ); 
                    END IF;

                    IF (IFNULL(NEW.bairro, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "cliente", "insert", "id", NEW.id, @client_addr, "bairro", 
                          NEW.bairro
                        ); 
                    END IF;

                    IF (IFNULL(NEW.cidade, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "cliente", "insert", "id", NEW.id, @client_addr, "cidade", 
                          NEW.cidade
                        ); 
                    END IF;

                    IF (IFNULL(NEW.uf, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "cliente", "insert", "id", NEW.id, @client_addr, "uf", 
                          NEW.uf
                        ); 
                    END IF;

                    IF (IFNULL(NEW.geo_lat, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "cliente", "insert", "id", NEW.id, @client_addr, "geo_lat", 
                          NEW.geo_lat
                        ); 
                    END IF;

                    IF (IFNULL(NEW.geo_lng, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "cliente", "insert", "id", NEW.id, @client_addr, "geo_lng", 
                          NEW.geo_lng
                        ); 
                    END IF;                   

                    IF (IFNULL(NEW.hr_ini_coleta_man, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "cliente", "insert", "id", NEW.id, @client_addr, "hr_ini_coleta_man", 
                          NEW.hr_ini_coleta_man
                        ); 
                    END IF;

                    IF (IFNULL(NEW.hr_fim_coleta_man, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "cliente", "insert", "id", NEW.id, @client_addr, "hr_fim_coleta_man", 
                          NEW.hr_fim_coleta_man
                        ); 
                    END IF;

                    IF (IFNULL(NEW.hr_ini_coleta_tar, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "cliente", "insert", "id", NEW.id, @client_addr, "hr_ini_coleta_tar", 
                          NEW.hr_ini_coleta_tar
                        ); 
                    END IF;

                    IF (IFNULL(NEW.hr_fim_coleta_tar, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "cliente", "insert", "id", NEW.id, @client_addr, "hr_fim_coleta_tar", 
                          NEW.hr_fim_coleta_tar
                        ); 
                    END IF;

                    IF (IFNULL(NEW.hr_ini_entrega_man, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "cliente", "insert", "id", NEW.id, @client_addr, "hr_ini_entrega_man", 
                          NEW.hr_ini_entrega_man
                        ); 
                    END IF;

                    IF (IFNULL(NEW.hr_fim_entrega_man, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "cliente", "insert", "id", NEW.id, @client_addr, "hr_fim_entrega_man", 
                          NEW.hr_fim_entrega_man
                        ); 
                    END IF;

                    IF (IFNULL(NEW.hr_ini_entrega_tar, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "cliente", "insert", "id", NEW.id, @client_addr, "hr_ini_entrega_tar", 
                          NEW.hr_ini_entrega_tar
                        ); 
                    END IF;

                    IF (IFNULL(NEW.hr_fim_entrega_tar, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "cliente", "insert", "id", NEW.id, @client_addr, "hr_fim_entrega_tar", 
                          NEW.hr_fim_entrega_tar
                        ); 
					END IF;
					
					IF (IFNULL(NEW.local_distrib, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "cliente", "insert", "id", NEW.id, @client_addr, "local_distrib", 
                          NEW.local_distrib
                        ); 
                    END IF;

                    IF (IFNULL(NEW.dt_alt_cad, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "cliente", "insert", "id", NEW.id, @client_addr, "dt_alt_cad", 
                          NEW.dt_alt_cad
                        ); 
                    END IF;

                    IF (IFNULL(NEW.hr_alt_cad, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "cliente", "insert", "id", NEW.id, @client_addr, "hr_alt_cad", 
                          NEW.hr_alt_cad
                        ); 
                    END IF;

                    IF (IFNULL(NEW.user_id, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "cliente", "insert", "id", NEW.id, @client_addr, "user_id", 
                          NEW.user_id
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
		DB::unprepared('DROP TRIGGER IF EXISTS tr_cliente_gravar_log_after_insert');
	}
}
