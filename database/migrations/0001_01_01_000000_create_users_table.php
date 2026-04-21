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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            // Personal Information
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix', 10)->nullable(); // Ibinalik
            $table->string('sex', 10); // Dinagdag base sa ERD
            $table->date('date_of_birth');
            $table->string('house_number');
            $table->string('purok_street');

            // Contact & Authentication
            $table->string('contact_number', 20)->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password');

            // Attachments (Uploads)
            $table->string('id_photo_path')->nullable();
            $table->string('selfie_photo_path')->nullable();

            // Roles & System Flags
            $table->string('role', 20)->default('resident');
            $table->string('otp_code', 10)->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->timestamp('contact_verified_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('email_otp_code', 10)->nullable();
            $table->timestamp('email_otp_expires_at')->nullable();

            // Preferences & Verification
            $table->tinyInteger('wants_email_notification')->default(1);
            $table->tinyInteger('is_verified')->default(0);

            // THE LARAVEL WAY: Bagong Rejection Tracking Columns
            $table->text('rejection_reason')->nullable();
            $table->integer('rejection_count')->default(0);
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('locked_until')->nullable();

            $table->timestamp('terms_accepted_at')->nullable();
            $table->timestamps(); // created_at, updated_at
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
