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
        Schema::create('book_issues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->date('issue_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->enum('status', ['issued', 'returned', 'overdue', 'lost', 'renewed'])->default('issued');
            $table->decimal('fine_amount', 8, 2)->default(0);
            $table->boolean('fine_paid')->default(false);
            $table->text('notes')->nullable();
            $table->integer('renewals')->default(0);
            $table->json('renewal_history')->nullable();
            
            // Staff tracking
            $table->unsignedBigInteger('issued_by');
            $table->unsignedBigInteger('returned_to')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('issued_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('returned_to')->references('id')->on('users')->onDelete('set null');

            // Indexes for better performance
            $table->index(['book_id', 'status']);
            $table->index(['student_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['status', 'due_date']);
            $table->index(['issue_date', 'status']);
            $table->index(['return_date', 'status']);
            $table->index(['due_date', 'status']);
            $table->index('issued_by');
            $table->index('returned_to');
            $table->index('fine_paid');
            $table->index('created_at');

            // Composite indexes for common queries
            $table->index(['student_id', 'status', 'due_date']);
            $table->index(['book_id', 'student_id', 'status']);
            $table->index(['status', 'fine_amount', 'fine_paid']);
            
            // Unique constraint to prevent duplicate active issues
            $table->unique(['book_id', 'student_id', 'status'], 'unique_active_issue');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_issues');
    }
};
