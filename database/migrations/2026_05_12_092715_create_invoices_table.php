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
            $table->foreignId('registration_entry_id')->constrained('registration_entries')->onDelete('cascade');
            $table->foreignId('company_id')
                ->nullable()
                ->constrained('companies')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('normalized_license_plate')->nullable()->comment('Biển số chuẩn hóa');
            $table->decimal('amount', 12, 2)->default(0)->comment('Số tiền hóa đơn');
            $table->boolean('is_paid')->default(false)->comment('Đã thanh toán chưa');
            $table->boolean('is_issued')->default(false)->comment('Đã xuất hóa đơn hay chưa');
            $table->datetime('paid_at')->nullable()->comment('Thời gian thanh toán');
            $table->string('payment_method')->nullable()->comment('Phương thức thanh toán');
            $table->string('file_path')->nullable()->comment('Đường dẫn file PDF hóa đơn');
            $table->text('notes')->nullable()->comment('Ghi chú');
            $table->timestamps();

            // Indexes
            $table->index(['registration_entry_id', 'is_paid']);
            $table->index('company_id');
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
