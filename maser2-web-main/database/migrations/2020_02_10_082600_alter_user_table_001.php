<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class AlterUserTable001 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (Schema::hasTable('users')) {                            

            Schema::table('users', function ($table) {
                $table->string('api_token', 80)->after('password')
                                    ->unique()
                                    ->nullable()
                                    ->default(null);
            });
            
        }
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('users')) {
            
            Schema::table('users', function ($table) {
                $table->dropColumn('api_token');
            });           
            
        }
    }
}
