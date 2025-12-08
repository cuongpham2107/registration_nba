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
        Schema::table('register_directlies', function (Blueprint $table) {
            $table->dateTime('end_date')->nullable()->change();
            $table->boolean('is_priority')->default(false)->after('end_date');
        });
       
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('register_directlies', function (Blueprint $table) {
            $table->dateTime('end_date')->change();
            $table->dropColumn('is_priority');
        });
    }
};
