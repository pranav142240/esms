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
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->enum('type', [
                'general', 'academic', 'event', 'holiday', 'exam', 
                'fee', 'admission', 'sports', 'cultural', 'maintenance'
            ])->default('general');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('target_audience', [
                'all', 'students', 'teachers', 'parents', 'staff', 'specific_classes'
            ])->default('all');
            $table->json('class_ids')->nullable();
            $table->boolean('is_published')->default(false);
            $table->datetime('published_at')->nullable();
            $table->datetime('expires_at')->nullable();
            $table->boolean('is_urgent')->default(false);
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->unsignedInteger('view_count')->default(0);
            
            // Tracking fields
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            // Indexes for better performance
            $table->index(['type', 'is_published']);
            $table->index(['target_audience', 'is_published']);
            $table->index(['priority', 'is_published']);
            $table->index(['is_published', 'published_at']);
            $table->index(['is_published', 'expires_at']);
            $table->index(['is_urgent', 'is_published']);
            $table->index('created_by');
            $table->index('created_at');
            $table->index('view_count');
            
            // Composite indexes for common queries
            $table->index(['is_published', 'target_audience', 'created_at']);
            $table->index(['is_published', 'type', 'priority']);
            $table->index(['is_urgent', 'priority', 'created_at']);
            
            // Full-text search index
            $table->fullText(['title', 'content']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};
