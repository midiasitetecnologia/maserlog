<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (Schema::hasTable('distance_matrix')) {

            if (Schema::hasColumn('distance_matrix', 'api_service')) {
                DB::statement('UPDATE distance_matrix SET api_service = "google_cloud", api_limit = "10000" WHERE api_service IS NULL');
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (Schema::hasTable('distance_matrix')) {

            if (Schema::hasColumn('distance_matrix', 'api_service')) {
                DB::statement('UPDATE distance_matrix SET api_service = NULL, api_limit = NULL WHERE api_service IS NOT NULL');
            }
        }
    }
};