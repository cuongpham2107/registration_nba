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
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('papers');
            $table->string('type');
            $table->string('areas')->nullable();
            $table->string('license_plate')->nullable();
            $table->string('note')->nullable();
            $table->foreignId('registration_id')
                ->nullable()
                ->constrained('registrations')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('visitor_vehicle_fee_id')
                ->nullable()
                ->constrained('visitor_vehicle_fees')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('gathering_point_fee_id')
                ->nullable()
                ->constrained('gathering_point_fees')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
