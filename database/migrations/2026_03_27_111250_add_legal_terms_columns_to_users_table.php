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
        Schema::table('users', function (Blueprint $table) {
            Schema::table('users', function (Blueprint $table) {
            $table->timestamp('terms_accepted_at')->nullable()->after('is_verified');
            $table->string('signup_ip', 45)->nullable()->after('terms_accepted_at'); // 45 chars para suportado ang IPv6
        });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['terms_accepted_at', 'signup_ip']);
        });
        });
    }
};
