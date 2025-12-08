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
        Schema::create('register_directlies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('papers')->nullable();
            $table->string('address')->nullable();
            $table->string('bks')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('job')->nullable();
            $table->foreignId('card_id')
                ->nullable()
                ->constrained('cards')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->dateTime('actual_date_out')->nullable();
            $table->enum('status',['none','coming_in','came_out'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('register_directlies');
    }
};
