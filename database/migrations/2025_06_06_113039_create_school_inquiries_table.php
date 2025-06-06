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
        Schema::create('school_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('school_name');
            $table->string('school_email')->unique();
            $table->string('school_phone');
            $table->text('school_address');
            $table->string('contact_person_name');
            $table->string('contact_person_email');
            $table->string('contact_person_phone');
            $table->string('proposed_domain')->unique();
            $table->text('school_tagline')->nullable();
            $table->json('form_data'); // Store all dynamic form field data
            $table->enum('status', ['pending', 'under_review', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable(); // Internal notes by superadmin
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('superadmins')->onDelete('set null');
            $table->foreignId('converted_school_id')->nullable()->constrained('schools')->onDelete('set null');
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index(['status', 'submitted_at']);
            $table->index(['school_email']);
            $table->index(['proposed_domain']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_inquiries');
    }
};
