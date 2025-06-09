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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('isbn', 20)->nullable()->unique();
            $table->string('author');
            $table->string('publisher')->nullable();
            $table->year('publication_year')->nullable();
            $table->string('category', 100);
            $table->string('subject', 100)->nullable();
            $table->text('description')->nullable();
            $table->integer('total_copies')->default(1);
            $table->integer('available_copies')->default(1);
            $table->string('language', 50)->nullable();
            $table->string('edition', 50)->nullable();
            $table->integer('pages')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->string('location', 100)->nullable();
            $table->enum('status', ['active', 'inactive', 'lost', 'damaged'])->default('active');
            $table->string('book_code', 50)->unique();
            
            // Tracking fields
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            // Indexes for better performance
            $table->index(['category', 'status']);
            $table->index(['author', 'status']);
            $table->index(['subject', 'status']);
            $table->index(['title', 'status']);
            $table->index('book_code');
            $table->index('isbn');
            $table->index('status');
            $table->index('created_by');
            $table->index('created_at');
            
            // Full-text search index for better searching
            $table->fullText(['title', 'author', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
