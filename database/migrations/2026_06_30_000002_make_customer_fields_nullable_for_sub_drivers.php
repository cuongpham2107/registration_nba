<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('type')->nullable()->change();
            $table->string('areas')->nullable()->change();
            $table->string('license_plate')->nullable()->change();
            $table->foreignId('registration_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('type')->nullable(false)->change();
            $table->string('areas')->nullable(false)->change();
            $table->string('license_plate')->nullable(false)->change();
            $table->foreignId('registration_id')->nullable(false)->change();
        });
    }
};
