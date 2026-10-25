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
        Schema::create('resident_masterlist', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix', 10)->nullable();
            $table->string('sex', 10);
            $table->date('date_of_birth');
            $table->string('address');
            $table->boolean('is_alive')->default(true); // Flag to check if resident is still active
            $table->timestamps();

            // Added unique constraint to support modern Laravel upsert() bulk insertion
            $table->unique(['first_name', 'last_name', 'date_of_birth'], 'resident_masterlist_unique_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resident_masterlist');
    }
};
