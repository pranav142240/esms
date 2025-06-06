<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */    public function up(): void
    {
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Field name (e.g., 'school_establishment_year')
            $table->string('label'); // Display label (e.g., 'Establishment Year')
            $table->enum('type', ['text', 'email', 'phone', 'textarea', 'select', 'file', 'number', 'date', 'url', 'radio', 'checkbox', 'time', 'datetime', 'password']);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_default')->default(false); // Default system fields
            $table->json('options')->nullable(); // For select fields, file types, etc.
            $table->json('validation_rules')->nullable(); // Custom validation rules
            $table->string('placeholder')->nullable();
            $table->text('help_text')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index(['is_active', 'sort_order']);
            $table->index(['is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
