<?php

use Illuminate\Database\Migrations\Migration;

class AlterVeiculoGravarLogAfterDeleteTrigger002 extends Migration
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_veiculo_gravar_log_after_delete');
        DB::unprepared('
            CREATE TRIGGER tr_veiculo_gravar_log_after_delete AFTER DELETE ON veiculo FOR EACH ROW
                BEGIN                
                    
                    -- As variaveis @uuid, @date_time serve como identificação para saber oque foi alterado nesse lote de dados.
                    SET @uuid = UUID(); 
                    SET @date_time = current_timestamp(6);
                    SET @client_addr = get_client_addr();

                    IF (IFNULL(OLD.descricao, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "veiculo", "delete", "placa", OLD.placa, @client_addr, "descricao", 
                          OLD.descricao
                        ); 
                    END IF;                   

                    IF (IFNULL(OLD.cod_tipo_veiculo, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "veiculo", "delete", "placa", OLD.placa, @client_addr, "cod_tipo_veiculo", OLD.cod_tipo_veiculo
                        ); 
                    END IF;                                                                                                     

                    IF (IFNULL(OLD.milk_run, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "veiculo", "delete", "placa", OLD.placa, @client_addr, "milk_run", OLD.milk_run
                        ); 
                    END IF;
                    
                    IF (IFNULL(OLD.sis_carga_empilha, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "veiculo", "delete", "placa", OLD.placa, @client_addr, "sis_carga_empilha", OLD.sis_carga_empilha
                        ); 
                    END IF;

                    IF (IFNULL(OLD.sis_carga_ponte, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "veiculo", "delete", "placa", OLD.placa, @client_addr, "sis_carga_ponte", OLD.sis_carga_ponte
                        ); 
                    END IF;

                    IF (IFNULL(OLD.sis_carga_manual, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "veiculo", "delete", "placa", OLD.placa, @client_addr, "sis_carga_manual", OLD.sis_carga_manual
                        ); 
                    END IF;

                    IF (IFNULL(OLD.largura, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "veiculo", "delete", "placa", OLD.placa, @client_addr, "largura", OLD.largura
                        ); 
                    END IF;                                                                                                     

                    IF (IFNULL(OLD.comprimento, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "veiculo", "delete", "placa", OLD.placa, @client_addr, "comprimento", OLD.comprimento
                        ); 
                    END IF;

                    IF (IFNULL(OLD.altura, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "veiculo", "delete", "placa", OLD.placa, @client_addr, "altura", OLD.altura
                        ); 
                    END IF;

                    IF (IFNULL(OLD.cap_cub, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "veiculo", "delete", "placa", OLD.placa, @client_addr, "cap_cub", OLD.cap_cub
                        ); 
                    END IF;

                    IF (IFNULL(OLD.cap_kg, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "veiculo", "delete", "placa", OLD.placa, @client_addr, "cap_kg", OLD.cap_kg
                        ); 
                    END IF;

                    IF (IFNULL(OLD.km_atual, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "veiculo", "delete", "placa", OLD.placa, @client_addr, "km_atual", OLD.km_atual
                        ); 
                    END IF;

                    IF (IFNULL(OLD.motorista_id, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "veiculo", "delete", "placa", OLD.placa, @client_addr, "motorista_id", OLD.motorista_id
                        ); 
                    END IF;

                    IF (IFNULL(OLD.ativo, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "veiculo", "delete", "placa", OLD.placa, @client_addr, "ativo", OLD.ativo
                        ); 
                    END IF;

                    IF (IFNULL(OLD.usar_gps, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "veiculo", "delete", "placa", OLD.placa, @client_addr, "usar_gps", OLD.usar_gps
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_veiculo_gravar_log_after_delete');
    }
}
