<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class UpdateColetaFixaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('coleta_fixa')) {

            if (Schema::hasColumn('coleta_fixa', 'autoriza_coleta')) {
                DB::statement('UPDATE coleta_fixa SET autoriza_coleta = "S" WHERE placa_coleta IS NOT NULL');
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('coleta_fixa')) {

            if (Schema::hasColumn('coleta_fixa', 'autoriza_coleta')) {
                DB::statement('UPDATE coleta_fixa SET autoriza_coleta = NULL');
            }
        }
    }
}
