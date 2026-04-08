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
            $table->foreignId('gathering_point_fee_id')
                ->nullable()
                ->constrained('gathering_point_fees')
                ->nullOnDelete();
            $table->foreignId('lifting_service_fee_id')
                ->nullable()
                ->constrained('lifting_service_fees')
                ->nullOnDelete();
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
        // Create customers table (FK to visitor_registrations)
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('papers');
            $table->string('type');
            $table->string('areas')->nullable();
            $table->string('license_plate')->nullable();
            $table->string('note')->nullable();
            $table->foreignId('visitor_registration_id')
                ->nullable()
                ->constrained('visitor_registrations')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('vehicle_registration_id')
                ->nullable()
                ->constrained('vehicle_registrations')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->unsignedBigInteger('visitor_vehicle_fee_id')->nullable();
            $table->timestamps();
        });

        // Create registration_entries table (actual entry/exit records for security guards)
        Schema::create('registration_entries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('papers')->nullable();
            $table->string('address')->nullable();
            $table->string('bks')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained('customers')
                ->onDelete('set null');
            $table->text('job')->nullable();
            $table->foreignId('card_id')
                ->nullable()
                ->constrained('cards')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreignId('vehicle_registration_id')
                ->nullable()
                ->constrained('vehicle_registrations')
                ->onDelete('set null');
            $table->foreignId('visitor_registration_id')
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
            $table->foreignId('registration_entry_id')
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
                $table->dropForeign(['registration_entry_id']);
            });
        } catch (Throwable $e) {
            // ignore
        }

        // Drop FK from registration_entries to vehicle_registrations (guarded)
        try {
            Schema::table('registration_entries', function (Blueprint $table) {
                $table->dropForeign(['vehicle_registration_id']);
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
