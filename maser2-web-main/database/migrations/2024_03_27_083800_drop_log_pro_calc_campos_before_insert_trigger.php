<?php

use Illuminate\Database\Migrations\Migration;

class DropLogProCalcCamposBeforeInsertTrigger extends Migration
{
    /**
     *          
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS tr_log_pro_calc_campos_before_insert');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('
            CREATE TRIGGER tr_log_pro_calc_campos_before_insert BEFORE INSERT ON log_pro FOR EACH ROW
                BEGIN                                  

                    -- Apenas o registro do tipo = "0" (Header) vai ter um proc_id nulo. Este
                    -- teste é apenas uma garantia, para as demais inclusões o proc_id já terá um valor.

                    IF ((NEW.tipo = "0") AND (IFNULL(NEW.proc_id, 0) = 0)) THEN

                        SET NEW.proc_id = (SELECT AUTO_INCREMENT 
                                           FROM information_schema.TABLES 
                                           WHERE TABLE_SCHEMA = DATABASE() 
                                           AND TABLE_NAME="log_pro");

                    END IF;                  

                END
        ');
    }
}
