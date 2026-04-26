<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if column exists before adding
        if (! Schema::hasColumn('register_directlies', 'id_registration_vehicle')) {
            Schema::table('register_directlies', function (Blueprint $table) {
                $table->foreignId('id_registration_vehicle')->nullable()->constrained('registration_vehicles')->onDelete('set null')->after('card_id');
            });
        }

        // Use raw SQL to modify enum field on MySQL only. Skip for SQLite (no MODIFY support).
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE registration_vehicles MODIFY COLUMN status ENUM('none', 'sent', 'approve', 'reject', 'entering', 'exited') DEFAULT 'none'");
        } else {
            // For sqlite or other drivers, altering enum-like columns isn't supported via MODIFY.
            // We skip here to avoid runtime errors on sqlite during local tests. If you need
            // to change the column type on non-mysql databases, perform a manual migration
            // using a table-recreate approach or use a database-specific tool.
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safely drop foreign key and column if they exist. Some DBs / states may not have
        // the foreign key present which causes DROP FOREIGN KEY to fail.
        if (Schema::hasTable('register_directlies') && Schema::hasColumn('register_directlies', 'id_registration_vehicle')) {
            try {
                Schema::table('register_directlies', function (Blueprint $table) {
                    $table->dropForeign(['id_registration_vehicle']);
                });
            } catch (\Throwable $e) {
                // ignore if the foreign key does not exist
            }

            // Drop the column if it still exists
            if (Schema::hasColumn('register_directlies', 'id_registration_vehicle')) {
                Schema::table('register_directlies', function (Blueprint $table) {
                    $table->dropColumn('id_registration_vehicle');
                });
            }
        }

        // Rollback enum to original values on MySQL only. Before changing, normalize any
        // values that are not in the target enum to 'none' to avoid ALTER failures.
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("UPDATE registration_vehicles SET status = 'none' WHERE status NOT IN ('none','sent','approve','reject') OR status IS NULL");
            DB::statement("ALTER TABLE registration_vehicles MODIFY COLUMN status ENUM('none', 'sent', 'approve', 'reject') DEFAULT 'none'");
        }
    }
};
