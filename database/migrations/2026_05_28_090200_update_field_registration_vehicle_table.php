<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('registration_vehicles', function (Blueprint $table) {
            $table->string('secret', 12)->nullable()->index()->after('status');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registration_vehicles', function (Blueprint $table) {
            $table->dropIndex(['secret']);
            $table->dropColumn('secret');
        });
    }
};
