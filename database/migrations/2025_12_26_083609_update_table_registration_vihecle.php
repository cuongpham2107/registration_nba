<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only run MySQL-specific ALTER to change column to TEXT
        try {
            $driver = DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);
        } catch (\Throwable $e) {
            return;
        }

        if ($driver !== 'mysql') {
            // Skip on non-mysql drivers
            return;
        }

        DB::statement('ALTER TABLE `registration_vehicles` MODIFY `hawb_number` TEXT NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only handle MySQL here. Truncate values >255 first to avoid ALTER failures.
        try {
            $driver = DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);
        } catch (\Throwable $e) {
            return;
        }

        if ($driver !== 'mysql') {
            return;
        }

        DB::statement('UPDATE registration_vehicles SET hawb_number = LEFT(hawb_number, 255) WHERE hawb_number IS NOT NULL AND CHAR_LENGTH(hawb_number) > 255');
        DB::statement('ALTER TABLE `registration_vehicles` MODIFY `hawb_number` VARCHAR(255) NOT NULL');
    }
};
