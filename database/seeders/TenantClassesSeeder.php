<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantClassesSeeder extends Seeder
{    /**
     * Seed tenant classes.
     */
    public function run(): void
    {
        // Seed Classes
        $classes = [
            ['name' => 'Class 1', 'grade_level' => 1, 'description' => 'First Grade', 'capacity' => 30, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Class 2', 'grade_level' => 2, 'description' => 'Second Grade', 'capacity' => 30, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Class 3', 'grade_level' => 3, 'description' => 'Third Grade', 'capacity' => 30, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Class 4', 'grade_level' => 4, 'description' => 'Fourth Grade', 'capacity' => 30, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Class 5', 'grade_level' => 5, 'description' => 'Fifth Grade', 'capacity' => 30, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Class 6', 'grade_level' => 6, 'description' => 'Sixth Grade', 'capacity' => 32, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Class 7', 'grade_level' => 7, 'description' => 'Seventh Grade', 'capacity' => 32, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Class 8', 'grade_level' => 8, 'description' => 'Eighth Grade', 'capacity' => 32, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Class 9', 'grade_level' => 9, 'description' => 'Ninth Grade', 'capacity' => 35, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Class 10', 'grade_level' => 10, 'description' => 'Tenth Grade', 'capacity' => 35, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('classes')->insert($classes);

        // Seed Sections for each class
        $sections = [];
        
        // Classes 1-3 have 2 sections each (A, B)
        for ($classId = 1; $classId <= 3; $classId++) {
            $sections[] = ['name' => 'A', 'class_id' => $classId, 'capacity' => 15, 'created_at' => now(), 'updated_at' => now()];
            $sections[] = ['name' => 'B', 'class_id' => $classId, 'capacity' => 15, 'created_at' => now(), 'updated_at' => now()];
        }
        
        // Classes 4-10 have 1 section each (A)
        for ($classId = 4; $classId <= 10; $classId++) {
            $sections[] = ['name' => 'A', 'class_id' => $classId, 'capacity' => 30, 'created_at' => now(), 'updated_at' => now()];
        }

        DB::table('sections')->insert($sections);
    }
}
