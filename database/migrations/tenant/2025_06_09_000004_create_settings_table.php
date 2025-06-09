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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->nullable();
            $table->string('type')->default('text'); // text, textarea, boolean, select, number
            $table->text('options')->nullable(); // JSON for select options
            $table->string('label');
            $table->text('description')->nullable();
            $table->boolean('is_private')->default(false);
            $table->timestamps();
        });

        // Insert default settings
        DB::table('settings')->insert([
            [
                'key' => 'school_name',
                'value' => tenant('id'),
                'group' => 'general',
                'type' => 'text',
                'options' => null,
                'label' => 'School Name',
                'description' => 'The name of your school or institution',
                'is_private' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'school_email',
                'value' => 'admin@' . tenant('id') . '.com',
                'group' => 'general',
                'type' => 'text',
                'options' => null,
                'label' => 'School Email',
                'description' => 'The primary contact email for your school',
                'is_private' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'school_phone',
                'value' => '+1234567890',
                'group' => 'general',
                'type' => 'text',
                'options' => null,
                'label' => 'School Phone',
                'description' => 'The primary contact phone for your school',
                'is_private' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'school_address',
                'value' => '123 School Street, City, State, Country',
                'group' => 'general',
                'type' => 'textarea',
                'options' => null,
                'label' => 'School Address',
                'description' => 'The physical address of your school',
                'is_private' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'system_timezone',
                'value' => 'UTC',
                'group' => 'system',
                'type' => 'select',
                'options' => json_encode([
                    'UTC' => 'UTC',
                    'America/New_York' => 'Eastern Time',
                    'America/Chicago' => 'Central Time',
                    'America/Denver' => 'Mountain Time',
                    'America/Los_Angeles' => 'Pacific Time',
                    'Asia/Kolkata' => 'Indian Standard Time',
                ]),
                'label' => 'System Timezone',
                'description' => 'The timezone for date and time display',
                'is_private' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'date_format',
                'value' => 'Y-m-d',
                'group' => 'system',
                'type' => 'select',
                'options' => json_encode([
                    'Y-m-d' => 'YYYY-MM-DD',
                    'm/d/Y' => 'MM/DD/YYYY',
                    'd/m/Y' => 'DD/MM/YYYY',
                    'M j, Y' => 'Month Day, Year',
                ]),
                'label' => 'Date Format',
                'description' => 'The format for displaying dates',
                'is_private' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
