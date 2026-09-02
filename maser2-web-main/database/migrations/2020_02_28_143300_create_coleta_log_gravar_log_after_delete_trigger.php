<?php

use Illuminate\Database\Migrations\Migration;

class CreateColetaLogGravarLogAfterDeleteTrigger extends Migration
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_coleta_log_gravar_log_after_delete');
        DB::unprepared('
            CREATE TRIGGER tr_coleta_log_gravar_log_after_delete AFTER DELETE ON coleta_log FOR EACH ROW
                BEGIN                
                    
                    -- As variaveis @uuid, @date_time serve como identificação para saber oque foi alterado nesse lote de dados.
                    SET @uuid = UUID(); 
                    SET @date_time = current_timestamp(6);
                    SET @client_addr = get_client_addr();

                    IF (IFNULL(OLD.coleta_id, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta_log", "delete", "id", OLD.id, @client_addr, "coleta_id", OLD.coleta_id
                        );
                    END IF;                    

                    IF (IFNULL(OLD.tipo, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta_log", "delete", "id", OLD.id, @client_addr, "tipo", 
                          OLD.tipo
                        ); 
                    END IF;                   

                    IF (IFNULL(OLD.descricao, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta_log", "delete", "id", OLD.id, @client_addr, "descricao", OLD.descricao
                        ); 
                    END IF;                                                                                                     

                    IF (IFNULL(OLD.texto, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value_blob
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta_log", "delete", "id", OLD.id, @client_addr, "texto", OLD.texto
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_coleta_log_gravar_log_after_delete');
    }
}
