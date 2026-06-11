<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('register_directlies', function (Blueprint $table) {
            $table->foreignId('registration_id')
                ->nullable()
                ->after('id_registration_vehicle')
                ->constrained('registrations')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('register_directlies', function (Blueprint $table) {
            $table->dropForeign(['registration_id']);
            $table->dropColumn('registration_id');
        });
    }
};
