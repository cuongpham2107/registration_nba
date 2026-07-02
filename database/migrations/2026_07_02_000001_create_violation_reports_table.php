<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('violation_reports', function (Blueprint $table) {
            $table->id();
            $table->dateTime('recorded_at');
            $table->string('location');
            $table->json('reporters');
            $table->json('witnesses');
            $table->json('violators');
            $table->string('target')->nullable();
            $table->text('violation_content');
            $table->string('violation_count')->nullable();
            $table->text('violator_attitude')->nullable();
            $table->text('resolution_direction')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violation_reports');
    }
};
