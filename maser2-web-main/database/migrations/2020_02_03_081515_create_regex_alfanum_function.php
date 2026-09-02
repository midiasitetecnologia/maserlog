<?php

use Illuminate\Database\Migrations\Migration;

class CreateRegexAlfanumFunction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // '$pattern' define os caracteres que serão eliminados da string. 
        
        // * * A T E N Ç Ã O !!  JAMAIS mude a ordem ou qualquer caracter nesta string, 
        // se você não souber o que está fazendo. Isso deu muito trabalho para fazer. 
        // Dependendo da ordem que coloca os caracteres não funciona. Fizemos a função 
        // no MySQL 'regex_replace' que faz a mesma coisa, porém, a ordem dos caracteres 
        // é ligeiramente diferente. Evandro, Jonas e Ricardo: 10/07/2017 - 09:58

        $pattern_str = '[][><}{)(:;,!?*%~^-`&#@ $¨_+=|' . '\\\\.' . '\/"' . "\'´" . '-]';

        /* Concatenamos uma ASPAS SIMPLES no início e no final do " SET pattern " para o MySQL
           entender que $pattern é uma string, se não colocar assim, dá erro. */
           
        $sql = 
            "            
            
            CREATE FUNCTION `regex_alfanum`(original VARCHAR(1000))            

            RETURNS VARCHAR(1000)
            DETERMINISTIC

            BEGIN 

              DECLARE pattern VARCHAR(100);                                        

              SET pattern = " . "'" . $pattern_str . "'; " . 

              "RETURN regex_replace(pattern, '', original);

            END;

            ";


        DB::connection()->getPdo()->exec($sql);

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
