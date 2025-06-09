<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\TenantDatabaseSeeder;

class SeedTenantData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:seed {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed tenant database with test data for API testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (app()->environment('production') && !$this->option('force')) {
            $this->error('Command disabled in production. Use --force to override.');
            return 1;
        }

        $this->info('Starting tenant data seeding...');
        
        try {
            $this->call('db:seed', [
                '--class' => TenantDatabaseSeeder::class
            ]);
            
            $this->info('✅ Tenant data seeded successfully!');
            $this->line('');
            $this->line('🎯 <fg=yellow>Next steps:</fg=yellow>');
            $this->line('1. Use the updated Postman collection to test tenant APIs');
            $this->line('2. Test login with seeded user credentials');
            $this->line('3. Explore all the filter options in the API endpoints');
            $this->line('');
            $this->line('📊 <fg=green>Data seeded:</fg=green>');
            $this->line('• Users: Admins, Teachers, Students, Parents');
            $this->line('• Academic: 12 Classes, 10 Subjects, 12 Students');
            $this->line('• Financial: Student Fees (144 records), Expenses (150+ records)');
            $this->line('• Library: 15 Books with issue/return records');
            $this->line('• Exams: 5 Types with results for all students');
            $this->line('• Notices: 20+ notices with different categories');
            
        } catch (\Exception $e) {
            $this->error('❌ Error seeding tenant data: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
