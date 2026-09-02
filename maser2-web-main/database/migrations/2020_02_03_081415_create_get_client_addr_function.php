<?php

use Illuminate\Database\Migrations\Migration;

class CreateGetClientAddrFunction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = '
            CREATE FUNCTION `get_client_addr`()
                RETURNS VARCHAR(255)
                DETERMINISTIC
            BEGIN  
                DECLARE client_addr VARCHAR(255);
  
                SET client_addr = 
                    (SELECT SUBSTRING_INDEX(host,":",1) FROM information_schema.processlist WHERE ID = CONNECTION_ID());  

                RETURN client_addr;

            END;';

        DB::connection()->getPdo()->exec($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sql = "DROP FUNCTION IF EXISTS get_client_addr";
        DB::connection()->getPdo()->exec($sql);
    }
}
