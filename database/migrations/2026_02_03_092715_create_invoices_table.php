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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_code')->unique()->comment('Mã hóa đơn');
            $table->foreignId('register_directly_id')->constrained('register_directlies')->onDelete('cascade');
            $table->string('normalized_license_plate')->nullable()->comment('Biển số chuẩn hóa để liên kết với car_catalogs');
            $table->foreignId('car_catalog_id')->nullable()->constrained('car_catalogs')->onDelete('set null');
            $table->decimal('amount', 12, 2)->default(0)->comment('Số tiền hóa đơn');
            $table->boolean('is_paid')->default(false)->comment('Đã thanh toán chưa');
            $table->datetime('paid_at')->nullable()->comment('Thời gian thanh toán');
            $table->string('payment_method')->nullable()->comment('Phương thức thanh toán');
            $table->string('file_path')->nullable()->comment('Đường dẫn file PDF hóa đơn');
            $table->text('notes')->nullable()->comment('Ghi chú');
            $table->timestamps();
            
            // Indexes
            $table->index(['register_directly_id', 'is_paid']);
            $table->index('normalized_license_plate');
            $table->index('invoice_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
