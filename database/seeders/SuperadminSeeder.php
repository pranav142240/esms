<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Domain\Superadmin\Models\Superadmin;

class SuperadminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Superadmin::create([
            'name' => 'System Administrator',
            'email' => 'superadmin@esms.com',
            'password' => Hash::make('SuperAdmin123!'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->command->info('Superadmin created successfully!');
        $this->command->line('Email: superadmin@esms.com');
        $this->command->line('Password: SuperAdmin123!');
    }
}
