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
        Schema::create('car_catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('license_plate');
            $table->foreignId('unit_id')
                ->constrained('units')
                ->onDelete('cascade');
            $table->enum('billing_type', ['prepaid', 'postpaid'])->default('prepaid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_catalogs');
    }
};
