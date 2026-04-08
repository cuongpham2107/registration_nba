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
        // Create visitor_registrations table (visitor registration for work visits)
        Schema::create('visitor_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('purpose')->nullable();
            $table->string('bks')->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->enum('status', ['sent', 'not_yet_sent'])->nullable();
            $table->foreignId('approver_id')
                ->nullable()
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->enum('type', ['browse', 'refuse'])->nullable();
            $table->dateTime('type_date')->nullable();
            $table->string('asset')->nullable();
            $table->string('note')->nullable();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->timestamps();
        });

        // Create customers table (FK to visitor_registrations)
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('papers');
            $table->string('type');
            $table->string('areas');
            $table->string('license_plate');
            $table->string('note')->nullable();
            $table->foreignId('visitor_registration_id')
                ->constrained('visitor_registrations')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unsignedBigInteger('visitor_vehicle_fee_id')->nullable();
            $table->timestamps();
        });

        // Create vehicle_registrations table (external vehicle registration for customs inspection)
        Schema::create('vehicle_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('driver_name');
            $table->string('name')->nullable();
            $table->string('driver_id_card');
            $table->string('driver_phone')->nullable();
            $table->string('vehicle_number');
            $table->boolean('is_priority')->default(false);
            $table->integer('sort')->nullable();
            $table->unsignedBigInteger('gathering_point_fee_id')->nullable();
            $table->unsignedBigInteger('lifting_service_fee_id')->nullable();
            $table->foreignId('company_id')
                ->nullable()
                ->constrained('companies')
                ->nullOnDelete();
            $table->integer('count_package')->nullable();
            $table->dateTime('expected_in_at');
            // Keep statuses aligned with guard workflow actions (e.g. GiveCardAction sets "entering").
            $table->enum('status', ['none', 'sent', 'approve', 'entering', 'exited', 'reject'])->default('none');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Create registration_entries table (actual entry/exit records for security guards)
        Schema::create('registration_entries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('papers')->nullable();
            $table->string('address')->nullable();
            $table->string('bks')->nullable();
            $table->foreignId('id_customer')->nullable()->constrained('customers')
                ->onDelete('set null');
            $table->text('job')->nullable();
            $table->foreignId('card_id')
                ->nullable()
                ->constrained('cards')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreignId('id_vehicle_registration')
                ->nullable()
                ->constrained('vehicle_registrations')
                ->onDelete('set null');
            $table->foreignId('id_visitor_registration')
                ->nullable()
                ->constrained('visitor_registrations')
                ->onDelete('set null');
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->boolean('is_priority')->default(false);
            $table->dateTime('actual_date_out')->nullable();
            $table->dateTime('actual_date_in')->nullable();
            $table->integer('sort')->nullable();
            $table->enum('type', ['vehicle', 'passenger'])->nullable();
            $table->string('areas')->nullable();
            $table->enum('status', ['none', 'coming_in', 'came_out'])->nullable();
            $table->timestamps();
        });

        // Add FK from vehicle_registrations to registration_entries (circular reference)
        Schema::table('vehicle_registrations', function (Blueprint $table) {
            $table->foreignId('id_registration_entry')
                ->nullable()
                ->constrained('registration_entries')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop circular FK first (guarded: may not exist depending on migration history)
        try {
            Schema::table('vehicle_registrations', function (Blueprint $table) {
                $table->dropForeign(['id_registration_entry']);
            });
        } catch (Throwable $e) {
            // ignore
        }

        // Drop FK from registration_entries to vehicle_registrations (guarded)
        try {
            Schema::table('registration_entries', function (Blueprint $table) {
                $table->dropForeign(['id_vehicle_registration']);
            });
        } catch (Throwable $e) {
            // ignore
        }

        Schema::dropIfExists('vehicle_registrations');
        Schema::dropIfExists('registration_entries');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('visitor_registrations');
    }
};
