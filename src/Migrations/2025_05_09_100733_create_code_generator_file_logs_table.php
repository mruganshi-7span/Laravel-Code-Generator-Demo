<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the `code_generator_file_logs` table to log file generation operations
     * performed by the code generator.
     */
    public function up(): void
    {
        Schema::create('code_generator_file_logs', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('file_type'); // Type of the file (e.g., Controller, Model, etc.)
            $table->string('file_path'); // Path where the file is generated
            $table->string('status'); // Status of the file generation (e.g., success, error)
            $table->text('message')->nullable(); // Optional message or description
            $table->boolean('is_overwrite')->default(false); // Indicates if the file was overwritten
            $table->timestamps(); // created_at and updated_at timestamps
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the `code_generator_file_logs` table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('code_generator_file_logs');
    }
};
