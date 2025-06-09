<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test admins for different phases
        $admins = [
            [
                'name' => 'John Smith',
                'email' => 'admin1@example.com',
                'password' => Hash::make('admin123'),
                'phone' => '+1234567890',
                'status' => Admin::STATUS_PENDING,
            ],
            [
                'name' => 'Jane Doe',
                'email' => 'admin2@example.com',
                'password' => Hash::make('admin123'),
                'phone' => '+1234567891',
                'status' => Admin::STATUS_ACTIVE,
            ],
            [
                'name' => 'Mike Wilson',
                'email' => 'admin3@example.com',
                'password' => Hash::make('admin123'),
                'phone' => '+1234567892',
                'status' => Admin::STATUS_SETTING_UP,
            ],
        ];

        foreach ($admins as $adminData) {
            Admin::create($adminData);
        }

        $this->command->info('Admin test data seeded successfully!');
    }
}
