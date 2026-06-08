<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql';

    public function up(): void
    {
        Schema::connection('mysql')->table('user_approvers', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
            $table->unique(['user_id', 'approver_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->table('user_approvers', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'approver_id']);
            $table->unique('user_id');
        });
    }
};
