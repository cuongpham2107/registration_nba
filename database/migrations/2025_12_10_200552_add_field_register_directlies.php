<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if column exists before adding
        if (!Schema::hasColumn('register_directlies', 'id_registration_vehicle')) {
            Schema::table('register_directlies', function (Blueprint $table) {
                $table->foreignId('id_registration_vehicle')->nullable()->constrained('registration_vehicles')->onDelete('set null')->after('card_id');
            });
        }
        
        // Use raw SQL to modify enum field
        DB::statement("ALTER TABLE registration_vehicles MODIFY COLUMN status ENUM('none', 'sent', 'approve', 'reject', 'entering', 'exited') DEFAULT 'none'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('register_directlies', function (Blueprint $table) {
            $table->dropForeign(['id_registration_vehicle']);
            $table->dropColumn('id_registration_vehicle');
        });
        
        // Rollback enum to original values using raw SQL
        DB::statement("ALTER TABLE registration_vehicles MODIFY COLUMN status ENUM('none', 'sent', 'approve', 'reject') DEFAULT 'none'");
    }
};
