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
        // 1. Table: service_requests (High-traffic queue queries & filtering)
        Schema::table('service_requests', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'status']);
            $table->index('queue_number');
            $table->index('document_type_id');
        });

        // 2. Table: audit_logs (Action & timestamp filtering)
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['created_at', 'action']);
            if (Schema::hasColumn('audit_logs', 'user_id')) {
                $table->index('user_id');
            } elseif (Schema::hasColumn('audit_logs', 'admin_id')) {
                $table->index('admin_id');
            }
        });

        // 3. Table: users (Verification status & OCR attempt filtering)
        Schema::table('users', function (Blueprint $table) {
            $table->index(['is_verified', 'ocr_attempts']);
            if (Schema::hasColumn('users', 'phone_number')) {
                $table->index('phone_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['queue_number']);
            $table->dropIndex(['document_type_id']);
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['created_at', 'action']);
            if (Schema::hasColumn('audit_logs', 'user_id')) {
                $table->dropIndex(['user_id']);
            } elseif (Schema::hasColumn('audit_logs', 'admin_id')) {
                $table->dropIndex(['admin_id']);
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_verified', 'ocr_attempts']);
            if (Schema::hasColumn('users', 'phone_number')) {
                $table->dropIndex(['phone_number']);
            }
        });
    }
};
