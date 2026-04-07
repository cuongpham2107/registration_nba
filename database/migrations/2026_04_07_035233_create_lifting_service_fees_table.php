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
        // Biểu phí đối với dịch vụ nâng hạ (vehicle_registrations)
        Schema::create('lifting_service_fees', function (Blueprint $table) {
            $table->id();
            $table->string('service_name')->comment('Tên dịch vụ: Nâng hạ container, nâng hạ hàng hóa...');
            $table->enum('weight_category', ['under_2_tons', 'over_2_tons'])->comment('Phân loại: Dưới 2 tấn / Trên 2 tấn');
            $table->integer('regular_hours_fee')->nullable()->comment('Giá giờ hành chính 7h30 - 16h30 (VNĐ)');
            $table->integer('four_hour_shift_fee')->nullable()->comment('Giá ca 4h (chỉ áp dụng trên 2 tấn) (VNĐ)');
            $table->integer('eight_hour_shift_fee')->nullable()->comment('Giá ca 8h (VNĐ)');
            $table->integer('after_hours_fee')->nullable()->comment('Giá sử dụng sau 16h30 (VNĐ)');
            $table->boolean('is_active')->default(true)->comment('Đang áp dụng');
            $table->text('notes')->nullable()->comment('Ghi chú');
            $table->timestamps();

            $table->index(['service_name', 'weight_category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lifting_service_fees');
    }
};
