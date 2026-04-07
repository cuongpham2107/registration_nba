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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            $table->string('name')->comment('Tên công ty');
            $table->string('tax_code')->nullable()->comment('Mã số thuế');
            $table->string('address')->nullable()->comment('Địa chỉ');
            $table->string('email')->nullable()->comment('Email');
            $table->string('phone')->nullable()->comment('Số điện thoại');

            $table->timestamps();

            $table->index('tax_code');
            $table->index('name');
            $table->unique('tax_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
