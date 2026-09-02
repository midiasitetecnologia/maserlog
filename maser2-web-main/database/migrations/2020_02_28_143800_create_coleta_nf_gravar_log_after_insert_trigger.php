<?php

use Illuminate\Database\Migrations\Migration;

class CreateColetaNfGravarLogAfterInsertTrigger extends Migration
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_coleta_nf_gravar_log_after_insert');
        DB::unprepared('
            CREATE TRIGGER tr_coleta_nf_gravar_log_after_insert AFTER INSERT ON coleta_nf FOR EACH ROW
                BEGIN                                    

                    -- As variaveis @uuid, @date_time serve como identificação para saber oque foi alterado nesse lote de dados.
                    SET @uuid = UUID();                    
                    SET @date_time = current_timestamp(6);
                    SET @client_addr = get_client_addr();

                    IF (IFNULL(NEW.coleta_id, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_nf", "insert", "id", NEW.id, @client_addr, "coleta_id", NEW.coleta_id
                        );                    
                    END IF;

                    IF (IFNULL(NEW.cod_barras, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_nf", "insert", "id", NEW.id, @client_addr, "cod_barras", NEW.cod_barras
                        );                    
                    END IF;

                    IF (IFNULL(NEW.serie, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_nf", "insert", "id", NEW.id, @client_addr, "serie", NEW.serie
                        );                    
                    END IF;

                    IF (IFNULL(NEW.numero, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_nf", "insert", "id", NEW.id, @client_addr, "numero", NEW.numero
                        );                    
                    END IF;

                    IF (IFNULL(NEW.valor, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_nf", "insert", "id", NEW.id, @client_addr, "valor", NEW.valor
                        );                    
                    END IF;

                    IF (IFNULL(NEW.volumes, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "coleta_nf", "insert", "id", NEW.id, @client_addr, "volumes", NEW.volumes
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_coleta_nf_gravar_log_after_insert');
    }
}
