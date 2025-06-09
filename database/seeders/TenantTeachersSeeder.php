<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantTeachersSeeder extends Seeder
{
    /**
     * Seed tenant teachers data.
     */
    public function run(): void
    {
        // First create additional users for teachers who don't have users yet
        $additionalUsers = [
            [
                'name' => 'Emily Davis',
                'email' => 'emily.davis@school.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'David Wilson',  
                'email' => 'david.wilson@school.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Lisa Anderson',
                'email' => 'lisa.anderson@school.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Robert Taylor',
                'email' => 'robert.taylor@school.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Maria Garcia',
                'email' => 'maria.garcia@school.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($additionalUsers as $userData) {
            $userId = DB::table('users')->insertGetId($userData);
            
            // Assign teacher role (assuming role ID 2)
            DB::table('model_has_roles')->insert([
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => $userId
            ]);
        }

        $teachers = [
            [
                'user_id' => 2, // Jane Smith from TenantUsersSeeder
                'employee_code' => 'EMP001',
                'name' => 'Jane Smith',
                'email' => 'jane.smith@school.com',
                'phone' => '+1234567901',
                'date_of_birth' => '1985-04-15',
                'gender' => 'female',
                'address' => '123 Teacher Lane, Education City',
                'qualification' => 'M.Ed in Mathematics',
                'experience_years' => 8,
                'joining_date' => '2020-08-15',
                'salary' => 55000.00,
                'department' => 'Mathematics',
                'designation' => 'Senior Teacher',
                'status' => 'active',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'user_id' => 3, // Sarah Johnson from TenantUsersSeeder
                'employee_code' => 'EMP002',
                'name' => 'Sarah Johnson',
                'email' => 'sarah.johnson@school.com',
                'phone' => '+1234567902',
                'date_of_birth' => '1988-09-22',
                'gender' => 'female',
                'address' => '456 Academic Avenue, Knowledge Park',
                'qualification' => 'M.A in English Literature',
                'experience_years' => 6,
                'joining_date' => '2021-01-10',
                'salary' => 52000.00,
                'department' => 'English',
                'designation' => 'Teacher',
                'status' => 'active',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'user_id' => 4, // Michael Brown from TenantUsersSeeder
                'employee_code' => 'EMP003',
                'name' => 'Michael Brown',
                'email' => 'michael.brown@school.com',
                'phone' => '+1234567903',
                'date_of_birth' => '1982-12-08',
                'gender' => 'male',
                'address' => '789 Science Street, Research Valley',
                'qualification' => 'M.Sc in Physics',
                'experience_years' => 12,
                'joining_date' => '2018-03-20',
                'salary' => 60000.00,
                'department' => 'Science',
                'designation' => 'Head of Department',
                'status' => 'active',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        // Get the newly created user IDs for additional teachers
        $emilyUser = DB::table('users')->where('email', 'emily.davis@school.com')->first();
        $davidUser = DB::table('users')->where('email', 'david.wilson@school.com')->first();
        $lisaUser = DB::table('users')->where('email', 'lisa.anderson@school.com')->first();
        $robertUser = DB::table('users')->where('email', 'robert.taylor@school.com')->first();
        $mariaUser = DB::table('users')->where('email', 'maria.garcia@school.com')->first();

        // Add additional teachers
        $additionalTeachers = [
            [
                'user_id' => $emilyUser->id,
                'employee_code' => 'EMP004',
                'name' => 'Emily Davis',
                'email' => 'emily.davis@school.com',
                'phone' => '+1234567904',
                'date_of_birth' => '1990-06-18',
                'gender' => 'female',
                'address' => '321 History Road, Culture District',
                'qualification' => 'M.A in History',
                'experience_years' => 4,
                'joining_date' => '2022-07-01',
                'salary' => 48000.00,
                'department' => 'Social Studies',
                'designation' => 'Teacher',
                'status' => 'active',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'user_id' => $davidUser->id,
                'employee_code' => 'EMP005',
                'name' => 'David Wilson',
                'email' => 'david.wilson@school.com',
                'phone' => '+1234567905',
                'date_of_birth' => '1987-11-30',
                'gender' => 'male',
                'address' => '654 Chemistry Lane, Lab City',
                'qualification' => 'M.Sc in Chemistry',
                'experience_years' => 7,
                'joining_date' => '2020-09-15',
                'salary' => 54000.00,
                'department' => 'Science',
                'designation' => 'Senior Teacher',
                'status' => 'active',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'user_id' => $lisaUser->id,
                'employee_code' => 'EMP006',
                'name' => 'Lisa Anderson',
                'email' => 'lisa.anderson@school.com',
                'phone' => '+1234567906',
                'date_of_birth' => '1989-03-12',
                'gender' => 'female',
                'address' => '987 Biology Boulevard, Nature Park',
                'qualification' => 'M.Sc in Biology',
                'experience_years' => 5,
                'joining_date' => '2021-08-01',
                'salary' => 50000.00,
                'department' => 'Science',
                'designation' => 'Teacher',
                'status' => 'active',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'user_id' => $robertUser->id,
                'employee_code' => 'EMP007',
                'name' => 'Robert Taylor',
                'email' => 'robert.taylor@school.com',
                'phone' => '+1234567907',
                'date_of_birth' => '1984-08-25',
                'gender' => 'male',
                'address' => '147 Computer Street, Tech Valley',
                'qualification' => 'M.Tech in Computer Science',
                'experience_years' => 9,
                'joining_date' => '2019-06-10',
                'salary' => 58000.00,
                'department' => 'Computer Science',
                'designation' => 'Senior Teacher',
                'status' => 'active',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'user_id' => $mariaUser->id,
                'employee_code' => 'EMP008',
                'name' => 'Maria Garcia',
                'email' => 'maria.garcia@school.com',
                'phone' => '+1234567908',
                'date_of_birth' => '1991-01-14',
                'gender' => 'female',
                'address' => '258 Sports Avenue, Athletic District',
                'qualification' => 'B.P.Ed in Physical Education',
                'experience_years' => 3,
                'joining_date' => '2023-04-05',
                'salary' => 45000.00,
                'department' => 'Physical Education',
                'designation' => 'Teacher',
                'status' => 'active',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        // Insert all teachers
        DB::table('teachers')->insert(array_merge($teachers, $additionalTeachers));
    }
}
