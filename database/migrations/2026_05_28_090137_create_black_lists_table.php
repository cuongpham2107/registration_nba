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
        Schema::create('black_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_vehicle_id')->constrained('registration_vehicles')->onDelete('cascade');
            $table->text('reason')->nullable();
            $table->foreignId('blacklisted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('blacklisted_at')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('black_lists');
    }
};
