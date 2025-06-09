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
        Schema::create('class_teacher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->enum('role', ['class_teacher', 'subject_teacher', 'assistant'])->default('subject_teacher');
            $table->timestamps();

            // Unique constraint to prevent duplicate assignments with same role
            $table->unique(['class_id', 'teacher_id', 'role']);
            
            // Indexes
            $table->index(['class_id']);
            $table->index(['teacher_id']);
            $table->index(['role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_teacher');
    }
};
