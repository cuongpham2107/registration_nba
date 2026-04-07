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
        // Biểu phí đối với phương tiện ra vào làm việc (visitor_registrations)
        Schema::create('visitor_vehicle_fees', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_type')->comment('Loại phương tiện: Xe máy, Ô tô dưới 9 chỗ, Xe tải...');
            $table->integer('per_visit_fee')->default(0)->comment('Mức phí theo lượt (VNĐ)');
            $table->integer('monthly_fee')->default(0)->comment('Mức phí theo tháng (VNĐ)');
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
        Schema::dropIfExists('visitor_vehicle_fees');
    }
};
