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
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_type')->comment('Loại phương tiện');
            $table->integer('full_day_fee')->default(0)->comment('Giá cả ngày giờ hành chính 7h - 17h (VNĐ) / 1 block 4h ');
            $table->integer('night_fee')->default(0)->comment('Giá sau 17h đến 7h sáng hôm sau (VNĐ) / 1 block 4h');
            $table->boolean('is_active')->default(true)->comment('Đang áp dụng');
            $table->text('notes')->nullable()->comment('Ghi chú');
            $table->timestamps();
            $table->index('vehicle_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};
