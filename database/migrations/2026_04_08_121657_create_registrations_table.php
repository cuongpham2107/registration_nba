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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Đơn vị
            $table->string('purpose')->nullable(); // mục đích
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->enum('type', ['working', 'inspection'])->nullable(); // loại ['Ra vào làm việc, 'Ra vào kiểm hoá']
            $table->enum('status', ['none', 'sent', 'approve', 'entering', 'exited', 'reject'])->default('none'); // 'Chưa duyệt', 'Đã gửi', 'Đã phê duyệt', 'Đang vào', 'Đã ra', 'Đã từ chối'
            $table->foreignId('approver_id')
                ->nullable()
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('cascade'); // người duyệt
            $table->dateTime('approved_at')->nullable(); // ngày duyệt
            $table->string('asset')->nullable(); // tài sản
            $table->string('note')->nullable(); // ghi chú
            $table->foreignId('user_id')
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('cascade'); // người tạo
            $table->foreignId('company_id')
                ->nullable()
                ->constrained('companies')
                ->onUpdate('cascade')
                ->onDelete('set null'); // công ty (đơn vị)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
