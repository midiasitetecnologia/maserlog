<?php

use Illuminate\Database\Migrations\Migration;

class AlterRegexAlfanumFunction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = "DROP FUNCTION IF EXISTS regex_alfanum";
        DB::connection()->getPdo()->exec($sql);

        // '$pattern' define os caracteres que serão eliminados da string. 

        // * * A T E N Ç Ã O !!  JAMAIS mude a ordem ou qualquer caracter nesta string, 
        // se você não souber o que está fazendo. Isso deu muito trabalho para fazer. 
        // Dependendo da ordem que coloca os caracteres não funciona. Fizemos a função 
        // no MySQL 'regex_replace' que faz a mesma coisa, porém, a ordem dos caracteres 
        // é ligeiramente diferente. Evandro, Jonas e Ricardo: 10/07/2017 - 09:58

        DB::unprepared('
            CREATE FUNCTION `regex_alfanum`(`original` VARCHAR(1000))
            RETURNS varchar(1000) CHARSET latin1
            LANGUAGE SQL
            DETERMINISTIC
            CONTAINS SQL
            SQL SECURITY DEFINER
            COMMENT ""
            BEGIN 

                -- pattern compativel com mysql 8

                DECLARE pattern VARCHAR(100);
                SET pattern = \'[\\]\\\[><}{)(:;,!?*%~^`&#@ $¨_+=|\\\\\\\.\\\\/"\\\'´\\-]\';                
                RETURN regex_replace(pattern, \'\', original);

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
        $sql = "DROP FUNCTION IF EXISTS regex_alfanum";
        DB::connection()->getPdo()->exec($sql);
    }
}
