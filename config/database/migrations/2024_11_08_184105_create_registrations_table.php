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
            $table->string('name');
            $table->string('purpose')->nullable();
            $table->string('bks')->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->enum('status',['sent', 'not_yet_sent'])->nullable(); // trạng thái: Đã gửi/chưa gửi
            $table->enum('type',['browse', 'refuse'])->nullable();//type: Duyệt/ từ chối
            $table->dateTime('type_date')->nullable();
            $table->string('asset')->nullable();
            $table->string('note')->nullable();
            $table->foreignId('user_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
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
