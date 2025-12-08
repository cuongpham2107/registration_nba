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
        Schema::table('registration_vehicles', function (Blueprint $table) {
            if (! Schema::hasColumn('registration_vehicles', 'pcs')) {
                $table->string('pcs')->nullable()->after('hawb_number');
                //Ưu tiên hay không
                $table->boolean('is_priority')->default(false)->after('pcs');
            }
            //drop column expected_out_at if exists
            if (Schema::hasColumn('registration_vehicles', 'expected_out_at')) {
                $table->dropColumn('expected_out_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registration_vehicles', function (Blueprint $table) {
            $table->dropColumn('pcs');
            $table->dropColumn('is_priority');
            $table->dateTime('expected_out_at')->nullable()->after('expected_in_at');
        });
    }
};
