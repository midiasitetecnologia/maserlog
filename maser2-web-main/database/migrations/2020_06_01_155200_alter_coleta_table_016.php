<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColetaTable016 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('coleta')) {

            Schema::table('coleta', function (Blueprint $table) {
                $table->unsignedInteger('nf_frete')->nullable()->after('receber_nf_frete');
                $table->string('sit_nf_frete', 1)->nullable()->after('nf_frete');
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
        if (Schema::hasTable('coleta')) {

            if (Schema::hasColumn('coleta', 'nf_frete')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('nf_frete');
                });
            }

            if (Schema::hasColumn('coleta', 'sit_nf_frete')) {

                Schema::table('coleta', function (Blueprint $table) {
                    $table->dropColumn('sit_nf_frete');
                });
            }
        }
    }
}
