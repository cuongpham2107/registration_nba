<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Chạy trên DB A (registration_nba)
    protected $connection = 'mysql';

    public function up(): void
    {
        Schema::connection('mysql')->create('user_approvers', function (Blueprint $table) {
            $table->id();
            // user cần được phê duyệt
            $table->unsignedBigInteger('user_id')->unique();
            // người phê duyệt
            $table->unsignedBigInteger('approver_id')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('approver_id');
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('user_approvers');
    }
};
