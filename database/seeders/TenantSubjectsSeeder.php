<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantSubjectsSeeder extends Seeder
{    /**
     * Seed tenant subjects.
     */
    public function run(): void
    {
        // Get first user as creator (should be admin)
        $adminUserId = DB::table('users')->first()->id;
        
        $subjects = [];
        
        // Core subjects for all classes (1-10)
        $coreSubjects = [
            ['name' => 'Mathematics', 'code' => 'MATH', 'description' => 'Mathematics and Arithmetic'],
            ['name' => 'English', 'code' => 'ENG', 'description' => 'English Language and Literature'],
        ];
        
        // Elementary subjects (Classes 1-5)
        $elementarySubjects = [
            ['name' => 'General Science', 'code' => 'SCI', 'description' => 'Basic Science'],
            ['name' => 'Social Studies', 'code' => 'SS', 'description' => 'Social Studies'],
            ['name' => 'Drawing', 'code' => 'ART', 'description' => 'Art and Drawing'],
        ];
        
        // Middle school subjects (Classes 6-8)
        $middleSubjects = [
            ['name' => 'Science', 'code' => 'SCI', 'description' => 'General Science'],
            ['name' => 'History', 'code' => 'HIST', 'description' => 'History'],
            ['name' => 'Geography', 'code' => 'GEO', 'description' => 'Geography'],
            ['name' => 'Computer Science', 'code' => 'CS', 'description' => 'Basic Computer Science'],
        ];
        
        // High school subjects (Classes 9-10)
        $highSubjects = [
            ['name' => 'Physics', 'code' => 'PHY', 'description' => 'Physics'],
            ['name' => 'Chemistry', 'code' => 'CHEM', 'description' => 'Chemistry'],
            ['name' => 'Biology', 'code' => 'BIO', 'description' => 'Biology'],
            ['name' => 'History', 'code' => 'HIST', 'description' => 'World History'],
            ['name' => 'Geography', 'code' => 'GEO', 'description' => 'Geography'],
            ['name' => 'Computer Science', 'code' => 'CS', 'description' => 'Computer Science'],
        ];
        
        // Add core subjects to all classes
        for ($classId = 1; $classId <= 10; $classId++) {
            foreach ($coreSubjects as $subject) {
                $subjects[] = [
                    'name' => $subject['name'],
                    'code' => $subject['code'] . str_pad($classId, 2, '0', STR_PAD_LEFT),
                    'description' => $subject['description'] . " for Class $classId",
                    'class_id' => $classId,
                    'created_by' => $adminUserId,
                    'credits' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }
        
        // Add elementary subjects (Classes 1-5)
        for ($classId = 1; $classId <= 5; $classId++) {
            foreach ($elementarySubjects as $subject) {
                $subjects[] = [
                    'name' => $subject['name'],
                    'code' => $subject['code'] . str_pad($classId, 2, '0', STR_PAD_LEFT),
                    'description' => $subject['description'] . " for Class $classId",
                    'class_id' => $classId,
                    'created_by' => $adminUserId,
                    'credits' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }
        
        // Add middle school subjects (Classes 6-8)
        for ($classId = 6; $classId <= 8; $classId++) {
            foreach ($middleSubjects as $subject) {
                $subjects[] = [
                    'name' => $subject['name'],
                    'code' => $subject['code'] . str_pad($classId, 2, '0', STR_PAD_LEFT),
                    'description' => $subject['description'] . " for Class $classId",
                    'class_id' => $classId,
                    'created_by' => $adminUserId,
                    'credits' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }
        
        // Add high school subjects (Classes 9-10)
        for ($classId = 9; $classId <= 10; $classId++) {
            foreach ($highSubjects as $subject) {
                $subjects[] = [
                    'name' => $subject['name'],
                    'code' => $subject['code'] . str_pad($classId, 2, '0', STR_PAD_LEFT),
                    'description' => $subject['description'] . " for Class $classId",
                    'class_id' => $classId,
                    'created_by' => $adminUserId,
                    'credits' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        DB::table('subjects')->insert($subjects);
    }
}
