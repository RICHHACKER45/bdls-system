<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Idinagdag natin ang boolean/tinyint(1) na column, default ay 1 (True)
            $table->boolean('wants_email_notification')->default(1)->after('signup_ip');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('wants_email_notification');
        });
    }
};