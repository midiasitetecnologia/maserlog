<?php

use Illuminate\Database\Migrations\Migration;

class CreateEmpresaGravarLogAfterDeleteTrigger extends Migration
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_empresa_gravar_log_after_delete');
        DB::unprepared('
            CREATE TRIGGER tr_empresa_gravar_log_after_delete AFTER DELETE ON empresa FOR EACH ROW
                BEGIN                
                    
                    -- As variaveis @uuid, @date_time serve como identificação para saber oque foi alterado nesse lote de dados.
                    SET @uuid = UUID(); 
                    SET @date_time = current_timestamp(6);
                    SET @client_addr = get_client_addr();                  

                    IF (IFNULL(OLD.nome, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "empresa", "delete", "codigo", OLD.codigo, @client_addr, "nome", 
                          OLD.nome
                        ); 
                    END IF;                   

                    IF (IFNULL(OLD.sigla, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "empresa", "delete", "codigo", OLD.codigo, @client_addr, "sigla", OLD.sigla
                        ); 
                    END IF;                                                                                                     

                    IF (IFNULL(OLD.cor_fonte, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "empresa", "delete", "codigo", OLD.codigo, @client_addr, "cor_fonte", OLD.cor_fonte
                        ); 
                    END IF;                                                                                                     

                    IF (IFNULL(OLD.cor_fundo, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "empresa", "delete", "codigo", OLD.codigo, @client_addr, "cor_fundo", OLD.cor_fundo
                        ); 
                    END IF;                                                                                                     

                    IF (IFNULL(OLD.icone, "") != "") THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value
                        ) 
                        VALUES 
                        ( @uuid, OLD.ass_user_id, @date_time, "empresa", "delete", "codigo", OLD.codigo, @client_addr, "icone", OLD.icone
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_empresa_gravar_log_after_delete');
    }
}
