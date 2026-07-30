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
        Schema::create('census_records', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix', 10)->nullable();
            $table->string('sex', 10);
            $table->date('date_of_birth');
            $table->string('address');
            $table->boolean('is_alive')->default(true); // Para alam kung active resident pa
            $table->timestamps();

            // THE FIX: Added unique constraint to support modern Laravel upsert() bulk insertion
            $table->unique(['first_name', 'last_name', 'date_of_birth'], 'census_records_unique_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('census_records');
    }
};
