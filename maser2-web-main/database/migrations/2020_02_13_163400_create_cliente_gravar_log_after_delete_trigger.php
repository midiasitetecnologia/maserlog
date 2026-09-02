<?php

use Illuminate\Database\Migrations\Migration;

class CreateClienteGravarLogAfterDeleteTrigger extends Migration
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_cliente_gravar_log_after_delete');
        DB::unprepared('
            CREATE TRIGGER tr_cliente_gravar_log_after_delete AFTER DELETE ON cliente FOR EACH ROW
                BEGIN                
                    
                    -- As variaveis @uuid, @date_time serve como identificação para saber oque foi alterado nesse lote de dados.
                    SET @uuid = UUID(); 
                    SET @date_time = current_timestamp(6);
                    SET @client_addr = get_client_addr();

                    IF (IFNULL(OLD.empresa, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "cliente", "delete", "id", OLD.id, @client_addr, "empresa", OLD.empresa
                        );
                    END IF;                    

                    IF (IFNULL(OLD.codigo, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "cliente", "delete", "id", OLD.id, @client_addr, "codigo", 
                          OLD.codigo
                        ); 
                    END IF;                   

                    IF (IFNULL(OLD.tipo_pessoa, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "cliente", "delete", "id", OLD.id, @client_addr, "tipo_pessoa", OLD.tipo_pessoa
                        ); 
                    END IF;                                                                                                     

                    IF (IFNULL(OLD.nome, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "cliente", "delete", "id", OLD.id, @client_addr, "nome", OLD.nome
                        ); 
                    END IF;                                                                                                     

                    IF (IFNULL(OLD.fantasia, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "cliente", "delete", "id", OLD.id, @client_addr, "fantasia", OLD.fantasia
                        ); 
                    END IF;                                                                                                     

                    IF (IFNULL(OLD.cpf_cnpj, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "cliente", "delete", "id", OLD.id, @client_addr, "cpf_cnpj", OLD.cpf_cnpj
                        ); 
                    END IF;

                    IF (IFNULL(OLD.fone, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "cliente", "delete", "id", OLD.id, @client_addr, "fone", OLD.fone
                        ); 
                    END IF;

                    IF (IFNULL(OLD.cep, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "cliente", "delete", "id", OLD.id, @client_addr, "cep", OLD.cep
                        ); 
                    END IF;

                    IF (IFNULL(OLD.endereco, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "cliente", "delete", "id", OLD.id, @client_addr, "endereco", OLD.endereco
                        ); 
                    END IF;

                    IF (IFNULL(OLD.bairro, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "cliente", "delete", "id", OLD.id, @client_addr, "bairro", OLD.bairro
                        ); 
                    END IF;

                    IF (IFNULL(OLD.cidade, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "cliente", "delete", "id", OLD.id, @client_addr, "cidade", OLD.cidade
                        ); 
                    END IF;

                    IF (IFNULL(OLD.uf, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "cliente", "delete", "id", OLD.id, @client_addr, "uf", OLD.uf
                        ); 
                    END IF;

                    IF (IFNULL(OLD.geo_lat, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "cliente", "delete", "id", OLD.id, @client_addr, "geo_lat", OLD.geo_lat
                        ); 
                    END IF;

                    IF (IFNULL(OLD.geo_lng, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "cliente", "delete", "id", OLD.id, @client_addr, "geo_lng", OLD.geo_lng
                        ); 
                    END IF;

                    IF (IFNULL(OLD.hr_ini_coleta_man, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "cliente", "delete", "id", OLD.id, @client_addr, "hr_ini_coleta_man", OLD.hr_ini_coleta_man
                        ); 
                    END IF;

                    IF (IFNULL(OLD.hr_fim_coleta_man, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "cliente", "delete", "id", OLD.id, @client_addr, "hr_fim_coleta_man", OLD.hr_fim_coleta_man
                        ); 
                    END IF;

                    IF (IFNULL(OLD.hr_ini_coleta_tar, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "cliente", "delete", "id", OLD.id, @client_addr, "hr_ini_coleta_tar", OLD.hr_ini_coleta_tar
                        ); 
                    END IF;

                    IF (IFNULL(OLD.hr_fim_coleta_tar, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "cliente", "delete", "id", OLD.id, @client_addr, "hr_fim_coleta_tar", OLD.hr_fim_coleta_tar
                        ); 
                    END IF;

                    IF (IFNULL(OLD.hr_ini_entrega_man, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "cliente", "delete", "id", OLD.id, @client_addr, "hr_ini_entrega_man", OLD.hr_ini_entrega_man
                        ); 
                    END IF;

                    IF (IFNULL(OLD.hr_fim_entrega_man, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "cliente", "delete", "id", OLD.id, @client_addr, "hr_fim_entrega_man", OLD.hr_fim_entrega_man
                        ); 
                    END IF;

                    IF (IFNULL(OLD.hr_ini_entrega_tar, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "cliente", "delete", "id", OLD.id, @client_addr, "hr_ini_entrega_tar", OLD.hr_ini_entrega_tar
                        ); 
                    END IF;

                    IF (IFNULL(OLD.hr_fim_entrega_tar, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "cliente", "delete", "id", OLD.id, @client_addr, "hr_fim_entrega_tar", OLD.hr_fim_entrega_tar
                        ); 
                    END IF;

                    IF (IFNULL(OLD.dt_alt_cad, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "cliente", "delete", "id", OLD.id, @client_addr, "dt_alt_cad", OLD.dt_alt_cad
                        ); 
                    END IF;

                    IF (IFNULL(OLD.hr_alt_cad, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "cliente", "delete", "id", OLD.id, @client_addr, "hr_alt_cad", OLD.hr_alt_cad
                        ); 
                    END IF;

                    IF (IFNULL(OLD.user_id, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "cliente", "delete", "id", OLD.id, @client_addr, "user_id", OLD.user_id
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_cliente_gravar_log_after_delete');
    }
}
