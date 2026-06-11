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
        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('is_invoiced')->default(false)->after('file_path')->comment('Đã xuất hóa đơn chưa');
            $table->datetime('invoiced_at')->nullable()->after('is_invoiced')->comment('Thời gian xuất hóa đơn');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['is_invoiced', 'invoiced_at']);
        });
    }
};
