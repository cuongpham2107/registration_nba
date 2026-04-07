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
        // Add FK to visitor_vehicle_fees
        Schema::table('visitor_registrations', function (Blueprint $table) {
            $table->foreign('visitor_vehicle_fee_id')
                ->references('id')
                ->on('visitor_vehicle_fees')
                ->onDelete('set null');
        });

        // Add FKs to gathering_point_fees and lifting_service_fees
        Schema::table('vehicle_registrations', function (Blueprint $table) {
            $table->foreign('gathering_point_fee_id')
                ->references('id')
                ->on('gathering_point_fees')
                ->onDelete('set null');
            $table->foreign('lifting_service_fee_id')
                ->references('id')
                ->on('lifting_service_fees')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitor_registrations', function (Blueprint $table) {
            $table->dropForeign(['visitor_vehicle_fee_id']);
        });

        Schema::table('vehicle_registrations', function (Blueprint $table) {
            $table->dropForeign(['gathering_point_fee_id']);
            $table->dropForeign(['lifting_service_fee_id']);
        });
    }
};
