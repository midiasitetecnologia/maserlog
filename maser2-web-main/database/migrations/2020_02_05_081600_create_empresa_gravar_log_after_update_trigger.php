<?php

use Illuminate\Database\Migrations\Migration;

class CreateEmpresaGravarLogAfterUpdateTrigger extends Migration
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_empresa_gravar_log_after_update');
        DB::unprepared('
            CREATE TRIGGER tr_empresa_gravar_log_after_update AFTER UPDATE ON empresa FOR EACH ROW
                BEGIN
                    
                    -- As variaveis @uuid, @date_time serve como identificação para saber oque foi alterado nesse lote de dados.
                    SET @uuid = UUID(); 
                    SET @date_time = current_timestamp(6);
                    SET @client_addr = get_client_addr();

                    IF (IFNULL(OLD.nome, "") != IFNULL(NEW.nome, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "empresa", "update", "codigo", NEW.codigo, @client_addr, "nome", OLD.nome, 
                          NEW.nome
                        );
                    END IF;                    

                    IF (IFNULL(OLD.sigla, "") != IFNULL(NEW.sigla, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "empresa", "update", "codigo", NEW.codigo, @client_addr, "sigla", 
                          OLD.sigla, NEW.sigla
                        ); 
                    END IF;

                    IF (IFNULL(OLD.cor_fonte, "") != IFNULL(NEW.cor_fonte, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "empresa", "update", "codigo", NEW.codigo, @client_addr, "cor_fonte", 
                          OLD.cor_fonte, NEW.cor_fonte
                        ); 
                    END IF;
                    
                    IF (IFNULL(OLD.cor_fundo, "") != IFNULL(NEW.cor_fundo, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "empresa", "update", "codigo", NEW.codigo, @client_addr, "cor_fundo", 
                          OLD.cor_fundo, NEW.cor_fundo
                        ); 
                    END IF;

                    IF (IFNULL(OLD.icone, "") != IFNULL(NEW.icone, "")) THEN
                        INSERT INTO user_log 
                        ( uuid, user_id, date_time, table_name, operation, pk, pk_value, client_addr, column_name, old_value, new_value
                        ) 
                        VALUES 
                        ( @uuid, NEW.ass_user_id, @date_time, "empresa", "update", "codigo", NEW.codigo, @client_addr, "icone", 
                          OLD.icone, NEW.icone
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
        DB::unprepared('DROP TRIGGER IF EXISTS tr_empresa_gravar_log_after_update');
    }
}
