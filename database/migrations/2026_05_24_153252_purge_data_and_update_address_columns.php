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
        // 1. Linisin ang Users Table at palitan ang purok_street sa address
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'selfie_photo_path',
                'rejection_reason',
                'rejection_count',
                'rejected_at',
                'locked_until',
                'house_number'
            ]);
            $table->renameColumn('purok_street', 'address');
        });

        // 2. Linisin ang Service Requests Table
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn('preferred_pickup_time');
        });
    }

    public function down(): void
    {
        // I-rollback sakaling magka-aberya
        Schema::table('users', function (Blueprint $table) {
            $table->string('selfie_photo_path')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->integer('rejection_count')->default(0);
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('locked_until')->nullable();
            $table->string('house_number')->nullable();
            
            $table->renameColumn('address', 'purok_street');
        });

        Schema::table('service_requests', function (Blueprint $table) {
            $table->dateTime('preferred_pickup_time')->nullable();
        });
    }  
};
