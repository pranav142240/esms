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
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->unique();
            $table->string('phone');
            $table->text('address');
            $table->string('logo_path')->nullable();
            $table->string('domain')->unique(); // e.g., school1.superadmindomain.com
            $table->string('school_code')->unique(); // e.g., SCH-2025-0001
            $table->text('tagline')->nullable();
            $table->foreignId('subscription_plan_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['active', 'inactive', 'suspended', 'terminated'])->default('inactive');
            $table->date('subscription_start_date')->nullable();
            $table->date('subscription_end_date')->nullable();
            $table->boolean('in_grace_period')->default(false);
            $table->date('grace_period_end_date')->nullable();
            $table->json('form_data')->nullable(); // Store additional registration form data
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('superadmins')->onDelete('set null');
            $table->string('database_name')->nullable(); // Tenant database name
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index(['status', 'subscription_end_date']);
            $table->index(['domain']);
            $table->index(['school_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
