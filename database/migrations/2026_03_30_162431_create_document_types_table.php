<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255); // Pangalan ng dokumento
            $table->text('requirements_description'); // Mga kinakailangang ID/Papel
            $table->tinyInteger('is_active')->default(1); // 1 = Active, 0 = Inactive
            $table->timestamps(); // created_at at updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_types');
    }
};
