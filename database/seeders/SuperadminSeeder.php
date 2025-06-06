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
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->command->info('Superadmin created successfully!');
        $this->command->line('Email: superadmin@example.com');
        $this->command->line('Password: password123');
    }
}
