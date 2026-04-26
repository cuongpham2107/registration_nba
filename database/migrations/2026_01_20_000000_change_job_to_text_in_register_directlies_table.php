<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations (MySQL only).
     */
    public function up(): void
    {
        if (! Schema::hasTable('register_directlies') || ! Schema::hasColumn('register_directlies', 'job')) {
            return;
        }

        try {
            $driver = DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);
        } catch (\Throwable $e) {
            // Unable to determine driver; skip
            return;
        }

        if ($driver !== 'mysql') {
            // This migration contains MySQL-specific SQL. Skip on other drivers.
            return;
        }

        // MySQL: change column type to TEXT
        DB::statement('ALTER TABLE `register_directlies` MODIFY `job` TEXT NULL');
    }

    /**
     * Reverse the migrations (MySQL only).
     */
    public function down(): void
    {
        if (! Schema::hasTable('register_directlies') || ! Schema::hasColumn('register_directlies', 'job')) {
            return;
        }

        try {
            $driver = DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);
        } catch (\Throwable $e) {
            // Unable to determine driver; skip
            return;
        }

        if ($driver !== 'mysql') {
            // Only handle MySQL here.
            return;
        }

        // Truncate values longer than 255 to avoid ALTER failures, then change column back to VARCHAR(255)
        DB::statement('UPDATE register_directlies SET job = LEFT(job, 255) WHERE job IS NOT NULL AND CHAR_LENGTH(job) > 255');
        DB::statement('ALTER TABLE `register_directlies` MODIFY `job` VARCHAR(255) NULL');
    }
};
