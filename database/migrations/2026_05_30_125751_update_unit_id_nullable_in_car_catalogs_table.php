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
        Schema::table('car_catalogs', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->foreignId('unit_id')->nullable()->change();
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('car_catalogs', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->foreignId('unit_id')->nullable(false)->change();
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
        });
    }
};
