<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE register_directlies MODIFY COLUMN status ENUM('none','coming_in','came_out','temporary_out') DEFAULT 'none' NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE register_directlies MODIFY COLUMN status ENUM('none','coming_in','came_out') DEFAULT 'none' NULL");
    }
};
