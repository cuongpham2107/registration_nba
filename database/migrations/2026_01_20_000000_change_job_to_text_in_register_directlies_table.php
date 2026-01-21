<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration attempts to ALTER the existing `job` column to TEXT
     * using a direct SQL statement for supported drivers (MySQL / PostgreSQL).
     * For other drivers (e.g. SQLite), it will throw an informative exception
     * so you can decide how to proceed.
     */
    public function up(): void
    {
        if (! Schema::hasTable('register_directlies') || ! Schema::hasColumn('register_directlies', 'job')) {
            return;
        }

        try {
            $driver = DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Unable to determine DB driver: ' . $e->getMessage());
        }

        try {
            if ($driver === 'mysql') {
                // MySQL: change column type to TEXT
                DB::statement('ALTER TABLE `register_directlies` MODIFY `job` TEXT NULL');
            } elseif ($driver === 'pgsql') {
                // PostgreSQL: change column type to text
                DB::statement('ALTER TABLE register_directlies ALTER COLUMN job TYPE TEXT');
            } else {
                throw new \RuntimeException('Direct ALTER of column type is not supported for your DB driver ('. $driver .'). If you need to proceed, either install doctrine/dbal and use Schema::table(...->change()), or run a migration that copies data to a new column.');
            }
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to alter `job` column to TEXT: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('register_directlies') || ! Schema::hasColumn('register_directlies', 'job')) {
            return;
        }

        try {
            $driver = DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Unable to determine DB driver: ' . $e->getMessage());
        }

        try {
            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE `register_directlies` MODIFY `job` VARCHAR(255) NULL');
            } elseif ($driver === 'pgsql') {
                DB::statement('ALTER TABLE register_directlies ALTER COLUMN job TYPE VARCHAR(255)');
            } else {
                throw new \RuntimeException('Direct ALTER of column type is not supported for your DB driver ('. $driver .').');
            }
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to revert `job` column to VARCHAR(255): ' . $e->getMessage());
        }
    }
};
