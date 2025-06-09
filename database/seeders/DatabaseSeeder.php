<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Core application seeders (always run)
        $this->call([
            SuperadminSeeder::class,
            SubscriptionPlansSeeder::class,
            FormFieldsSeeder::class,
        ]);

        // Check if we should run tenant seeders (for testing)
        if ($this->command->confirm('Do you want to seed tenant test data? (Only for testing purposes)', false)) {
            $this->command->info('Seeding tenant test data...');
            $this->call([
                TenantDatabaseSeeder::class,
            ]);
        }

        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
