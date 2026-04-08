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
        // Biểu phí đối với phương tiện ra vào "Địa điểm tập trung" (registrations)
        Schema::create('gathering_point_fees', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_type')->comment('Loại phương tiện');
            $table->integer('morning_fee')->default(0)->comment('Giá từ 7h - 12h (VNĐ)');
            $table->integer('afternoon_fee')->default(0)->comment('Giá từ 12h - 17h (VNĐ)');
            $table->integer('full_day_fee')->default(0)->comment('Giá cả ngày giờ hành chính 7h - 17h (VNĐ)');
            $table->integer('night_fee')->default(0)->comment('Giá sau 17h đến 7h sáng hôm sau (VNĐ)');
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
        Schema::dropIfExists('gathering_point_fees');
    }
};
