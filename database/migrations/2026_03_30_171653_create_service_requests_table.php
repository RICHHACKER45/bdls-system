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
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('document_type_id')->constrained('document_types')->cascadeOnDelete();
            $table->string('request_channel', 20); // Online, Walk-in
            $table->string('queue_number', 50)->nullable(); 
            $table->string('purpose', 255);
            $table->text('additional_details')->nullable();
            $table->dateTime('preferred_pickup_time');
            $table->string('status', 20)->default('Pending'); // Pending, For Interview, Processing, Released
            $table->timestamp('released_at')->nullable();
            $table->foreignId('released_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes(); // Ang deleted_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
