<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class TenantUsersSeeder extends Seeder
{
    /**
     * Seed tenant users.
     */
    public function run(): void
    {
        // Admin User
        $admin = User::create([
            'name' => 'School Administrator',
            'email' => 'admin@school.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        // Teacher Users
        $teacher1 = User::create([
            'name' => 'John Smith',
            'email' => 'john.smith@school.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        $teacher1->assignRole('teacher');

        $teacher2 = User::create([
            'name' => 'Sarah Johnson',
            'email' => 'sarah.johnson@school.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        $teacher2->assignRole('teacher');

        $teacher3 = User::create([
            'name' => 'Michael Brown',
            'email' => 'michael.brown@school.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        $teacher3->assignRole('teacher');

        // Accountant User
        $accountant = User::create([
            'name' => 'Lisa Davis',
            'email' => 'accountant@school.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        $accountant->assignRole('accountant');

        // Librarian User
        $librarian = User::create([
            'name' => 'Robert Wilson',
            'email' => 'librarian@school.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        $librarian->assignRole('librarian');

        // Student Users
        $students = [
            ['name' => 'Alice Cooper', 'email' => 'alice.cooper@student.school.com'],
            ['name' => 'Bob Martin', 'email' => 'bob.martin@student.school.com'],
            ['name' => 'Carol White', 'email' => 'carol.white@student.school.com'],
            ['name' => 'David Lee', 'email' => 'david.lee@student.school.com'],
            ['name' => 'Emma Taylor', 'email' => 'emma.taylor@student.school.com'],
            ['name' => 'Frank Miller', 'email' => 'frank.miller@student.school.com'],
            ['name' => 'Grace Anderson', 'email' => 'grace.anderson@student.school.com'],
            ['name' => 'Henry Thomas', 'email' => 'henry.thomas@student.school.com'],
            ['name' => 'Ivy Jackson', 'email' => 'ivy.jackson@student.school.com'],
            ['name' => 'Jack Harris', 'email' => 'jack.harris@student.school.com'],
        ];

        foreach ($students as $studentData) {
            $student = User::create([
                'name' => $studentData['name'],
                'email' => $studentData['email'],
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]);
            $student->assignRole('student');
        }

        // Parent Users
        $parents = [
            ['name' => 'Margaret Cooper', 'email' => 'margaret.cooper@parent.school.com'],
            ['name' => 'James Martin', 'email' => 'james.martin@parent.school.com'],
            ['name' => 'Patricia White', 'email' => 'patricia.white@parent.school.com'],
            ['name' => 'Richard Lee', 'email' => 'richard.lee@parent.school.com'],
            ['name' => 'Jennifer Taylor', 'email' => 'jennifer.taylor@parent.school.com'],
        ];

        foreach ($parents as $parentData) {
            $parent = User::create([
                'name' => $parentData['name'],
                'email' => $parentData['email'],
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]);
            $parent->assignRole('parent');
        }
    }
}
