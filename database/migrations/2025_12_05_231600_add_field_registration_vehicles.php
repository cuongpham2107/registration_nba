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
            $table->integer('sort')->nullable()->after('is_priority');
            $table->foreignId('id_registration_directly')->nullable()->after('sort')->constrained('register_directlies')->onDelete('set null');
        });

        Schema::table('register_directlies', function (Blueprint $table) {
            $table->integer('sort')->nullable()->after('actual_date_in');
            $table->enum('type', ['vehicle', 'passenger'])->nullable();//Passenger registration and vehicle registration
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registration_vehicles', function (Blueprint $table) {
            $table->dropForeign(['id_registration_directly']);
            $table->dropColumn('id_registration_directly');
            $table->dropColumn('sort');
        });
        Schema::table('register_directlies', function (Blueprint $table) {
            $table->dropColumn('sort');
            $table->dropColumn('type');
        });
    }
};
