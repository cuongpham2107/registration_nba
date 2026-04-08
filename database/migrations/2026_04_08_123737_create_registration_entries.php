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
        Schema::create('registration_entries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('papers')->nullable();
            $table->string('license_plate')->nullable();
            $table->foreignId('guest_id')
                ->nullable()
                ->constrained('guests')
                ->onDelete('set null');
            $table->text('job')->nullable();
            $table->foreignId('card_id')
                ->nullable()
                ->constrained('cards')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreignId('registration_id')
                ->nullable()
                ->constrained('registrations')
                ->onDelete('set null');
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->dateTime('actual_date_out')->nullable();
            $table->dateTime('actual_date_in')->nullable();
            $table->enum('type', ['working', 'inspection'])->nullable();
            $table->string('areas')->nullable();
            $table->enum('status', ['none', 'entering', 'exited'])->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_entries');
    }
};
