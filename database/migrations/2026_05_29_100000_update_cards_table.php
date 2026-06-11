<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropColumn('account_id');
            $table->string('type')->default('daily');
            $table->date('expiry_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->string('account_id');
            $table->dropColumn(['type', 'expiry_date']);
        });
    }
};
