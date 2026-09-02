<?php

use Illuminate\Database\Migrations\Migration;

class CreateColetaFixaBloqGravarLogAfterDeleteTrigger extends Migration
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_coleta_fixa_bloq_gravar_log_after_delete');
        DB::unprepared('
            CREATE TRIGGER tr_coleta_fixa_bloq_gravar_log_after_delete AFTER DELETE ON coleta_fixa_bloq FOR EACH ROW
                BEGIN                
                    
                    -- As variaveis @uuid, @date_time serve como identificação para saber oque foi alterado nesse lote de dados.
                    SET @uuid = UUID(); 
                    SET @date_time = current_timestamp(6);
                    SET @client_addr = get_client_addr();

                    IF (IFNULL(OLD.coleta_fixa_id, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta_fixa_bloq", "delete", "id", OLD.id, @client_addr, "coleta_fixa_id", OLD.coleta_fixa_id
                        );
                    END IF;                    

                    IF (IFNULL(OLD.dt_ini, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta_fixa_bloq", "delete", "id", OLD.id, @client_addr, "dt_ini", 
                          OLD.dt_ini
                        ); 
                    END IF;                   

                    IF (IFNULL(OLD.dt_fim, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta_fixa_bloq", "delete", "id", OLD.id, @client_addr, "dt_fim", OLD.dt_fim
                        ); 
                    END IF;                                                                                                     

                    IF (IFNULL(OLD.observ, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "coleta_fixa_bloq", "delete", "id", OLD.id, @client_addr, "observ", OLD.observ
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_coleta_fixa_bloq_gravar_log_after_delete');
    }
}
