<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantStudentsSeeder extends Seeder
{
    /**
     * Seed tenant students data.
     */
    public function run(): void
    {
        // Create academic session first
        $academicSession = DB::table('academic_sessions')->insertGetId([
            'name' => '2024-2025',
            'start_date' => '2024-04-01',
            'end_date' => '2025-03-31',
            'is_current' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $students = [];
        
        // Sample student data
        $studentData = [
            ['name' => 'Alice Cooper', 'email' => 'alice.cooper@student.school.com', 'class_id' => 1, 'section_id' => 1, 'roll' => '001', 'gender' => 'female', 'dob' => '2008-03-15', 'blood' => 'O+'],
            ['name' => 'Bob Martin', 'email' => 'bob.martin@student.school.com', 'class_id' => 1, 'section_id' => 1, 'roll' => '002', 'gender' => 'male', 'dob' => '2008-07-22', 'blood' => 'A+'],
            ['name' => 'Carol White', 'email' => 'carol.white@student.school.com', 'class_id' => 1, 'section_id' => 2, 'roll' => '003', 'gender' => 'female', 'dob' => '2007-11-08', 'blood' => 'B+'],
            ['name' => 'David Lee', 'email' => 'david.lee@student.school.com', 'class_id' => 2, 'section_id' => 3, 'roll' => '001', 'gender' => 'male', 'dob' => '2007-05-12', 'blood' => 'AB+'],
            ['name' => 'Emma Taylor', 'email' => 'emma.taylor@student.school.com', 'class_id' => 2, 'section_id' => 4, 'roll' => '002', 'gender' => 'female', 'dob' => '2006-09-18', 'blood' => 'O-'],
            ['name' => 'Frank Miller', 'email' => 'frank.miller@student.school.com', 'class_id' => 3, 'section_id' => 5, 'roll' => '001', 'gender' => 'male', 'dob' => '2006-12-03', 'blood' => 'A-'],
            ['name' => 'Grace Anderson', 'email' => 'grace.anderson@student.school.com', 'class_id' => 4, 'section_id' => 6, 'roll' => '001', 'gender' => 'female', 'dob' => '2005-04-25', 'blood' => 'B-'],
            ['name' => 'Henry Thomas', 'email' => 'henry.thomas@student.school.com', 'class_id' => 5, 'section_id' => 7, 'roll' => '001', 'gender' => 'male', 'dob' => '2005-08-14', 'blood' => 'AB-'],
            ['name' => 'Ivy Jackson', 'email' => 'ivy.jackson@student.school.com', 'class_id' => 6, 'section_id' => 8, 'roll' => '001', 'gender' => 'female', 'dob' => '2004-01-30', 'blood' => 'O+'],
            ['name' => 'Jack Harris', 'email' => 'jack.harris@student.school.com', 'class_id' => 7, 'section_id' => 9, 'roll' => '001', 'gender' => 'male', 'dob' => '2004-06-11', 'blood' => 'A+'],
            ['name' => 'Kelly Wilson', 'email' => 'kelly.wilson@student.school.com', 'class_id' => 8, 'section_id' => 10, 'roll' => '001', 'gender' => 'female', 'dob' => '2003-10-07', 'blood' => 'B+'],
            ['name' => 'Leo Brown', 'email' => 'leo.brown@student.school.com', 'class_id' => 9, 'section_id' => 11, 'roll' => '001', 'gender' => 'male', 'dob' => '2003-02-19', 'blood' => 'AB+'],
            ['name' => 'Mia Davis', 'email' => 'mia.davis@student.school.com', 'class_id' => 10, 'section_id' => 12, 'roll' => '001', 'gender' => 'female', 'dob' => '2002-08-05', 'blood' => 'O-'],
        ];

        foreach ($studentData as $index => $data) {
            // Create user first
            $userId = DB::table('users')->insertGetId([
                'name' => $data['name'],
                'email' => $data['email'],
                'email_verified_at' => now(),
                'password' => Hash::make('password'), // Default password
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Assign student role to user (assuming student role has ID 3)
            DB::table('model_has_roles')->insert([
                'role_id' => 3,
                'model_type' => 'App\\Models\\User',
                'model_id' => $userId
            ]);

            // Create student record
            $studentCode = 'STU' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            
            $students[] = [
                'user_id' => $userId,
                'student_code' => $studentCode,
                'roll_number' => $data['roll'],
                'class_id' => $data['class_id'],
                'section_id' => $data['section_id'],
                'session_id' => $academicSession,
                'admission_date' => '2024-01-15',
                'date_of_birth' => $data['dob'],
                'gender' => $data['gender'],
                'blood_group' => $data['blood'],
                'religion' => 'Not Specified',
                'phone' => '+1234567' . str_pad($index + 801, 3, '0', STR_PAD_LEFT),
                'address' => ($index + 1) . ' Student Street, Education City',
                'parent_phone' => '+1234567' . str_pad($index + 851, 3, '0', STR_PAD_LEFT),
                'emergency_contact' => '+1234567' . str_pad($index + 851, 3, '0', STR_PAD_LEFT),
                'status' => 'active',
                'notes' => 'Seeded student data',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        DB::table('students')->insert($students);
    }
}
