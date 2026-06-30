<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_cards', function (Blueprint $table) {
            $table->id();
            $table->integer('stt')->nullable();
            $table->string('card_number')->nullable();
            $table->string('unit')->nullable();
            $table->string('license_plate')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->date('expired_at')->nullable();
            $table->string('phone')->nullable();
            $table->string('source_section')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_cards');
    }
};
