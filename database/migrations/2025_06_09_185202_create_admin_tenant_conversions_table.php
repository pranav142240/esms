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
    {        Schema::create('admin_tenant_conversions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->unsignedBigInteger('tenant_id');
            $table->json('old_admin_data'); // Backup of original admin data
            $table->enum('conversion_status', ['initiated', 'completed', 'failed'])->default('initiated');
            $table->text('error_message')->nullable();
            $table->timestamp('converted_at')->useCurrent();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['admin_id', 'conversion_status']);
            $table->index('tenant_id');
            // Note: Foreign key constraints will be added later
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_tenant_conversions');
    }
};
