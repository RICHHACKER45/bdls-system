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
            $table->dropColumn('suffix');
        });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
             // Ibinabalik natin ito base sa orihinal mong structure na varchar(50)
            $table->string('suffix', 50)->nullable()->after('last_name');
        });
    }
};
