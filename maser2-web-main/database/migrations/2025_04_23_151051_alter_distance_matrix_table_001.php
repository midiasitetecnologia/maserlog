<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('distance_matrix')) {

            Schema::table('distance_matrix', function (Blueprint $table) {
                $table->string('api_service', 20)->nullable()->after('id');
                $table->string('api_account', 100)->nullable()->after('api_service');
                $table->unsignedInteger('api_limit')->nullable()->after('api_key');
                $table->string('api_priority', 1)->default('1')->nullable()->after('api_usage');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('distance_matrix')) {

            if (Schema::hasColumn('distance_matrix', 'api_service')) {

                Schema::table('distance_matrix', function (Blueprint $table) {
                    $table->dropColumn('api_service');
                });
            }

            if (Schema::hasColumn('distance_matrix', 'api_account')) {

                Schema::table('distance_matrix', function (Blueprint $table) {
                    $table->dropColumn('api_account');
                });
            }

            if (Schema::hasColumn('distance_matrix', 'api_limit')) {

                Schema::table('distance_matrix', function (Blueprint $table) {
                    $table->dropColumn('api_limit');
                });
            }

            if (Schema::hasColumn('distance_matrix', 'api_priority')) {

                Schema::table('distance_matrix', function (Blueprint $table) {
                    $table->dropColumn('api_priority');
                });
            }
        }
    }
};
