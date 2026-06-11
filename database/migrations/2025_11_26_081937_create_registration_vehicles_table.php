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
        Schema::create('registration_vehicles', function (Blueprint $table) {
            $table->id();
            //Họ và tên lái xe, Số CMND/CCCD, Biển số xe, Số Hawb, Ngày giờ dự kiến vào, Ngày giờ dự kiến ra, người tạo Ghi chú
            $table->string('driver_name');
            $table->string('driver_id_card');
            $table->string('driver_phone')->nullable();
            $table->string('vehicle_number');
            $table->string('hawb_number');
            $table->dateTime('expected_in_at');
            $table->dateTime('expected_out_at');
            $table->text('notes')->nullable();
            $table->enum('status', ['none', 'sent', 'approve', 'reject'])->default('none');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_vehicles');
       
    }
};
