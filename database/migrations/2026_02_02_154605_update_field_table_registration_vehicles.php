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
            $table->foreignId('price_list_id')->nullable()->constrained('price_lists')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registration_vehicles', function (Blueprint $table) {
            $table->dropForeign(['price_list_id']);
            $table->dropColumn('price_list_id');
        });
    }
};
