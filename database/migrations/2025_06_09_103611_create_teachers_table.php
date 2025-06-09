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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('employee_code')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->text('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->text('qualification')->nullable();
            $table->integer('experience_years')->default(0);
            $table->date('joining_date');
            $table->decimal('salary', 10, 2)->nullable();
            $table->string('department')->nullable();
            $table->string('designation')->nullable();
            $table->enum('status', ['active', 'inactive', 'terminated'])->default('active');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index(['status']);
            $table->index(['department']);
            $table->index(['email']);
            $table->index(['employee_code']);
            $table->index(['joining_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
