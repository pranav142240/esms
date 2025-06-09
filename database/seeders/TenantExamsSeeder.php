<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class TenantExamsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get classes, subjects, and admin user
        $classes = DB::table('classes')->get();
        $subjects = DB::table('subjects')->get();
        $students = DB::table('students')->get();
        $adminUser = DB::table('users')->where('email', 'admin@school.com')->first();
        $teachers = DB::table('teachers')->get();
        $teacherUserIds = $teachers->pluck('user_id')->toArray();

        // Default users if specific roles not found
        if (!$adminUser) {
            $adminUser = DB::table('users')->first();
        }

        // Exam types and their schedules
        $examTypes = [
            [
                'name' => 'First Unit Test',
                'type' => 'written',
                'month_offset' => -8, // 8 months ago
                'duration' => 90,
                'total_marks' => 50,
            ],
            [
                'name' => 'Second Unit Test',
                'type' => 'written',
                'month_offset' => -6, // 6 months ago
                'duration' => 90,
                'total_marks' => 50,
            ],
            [
                'name' => 'Half Yearly Examination',
                'type' => 'written',
                'month_offset' => -4, // 4 months ago
                'duration' => 180,
                'total_marks' => 100,
            ],
            [
                'name' => 'Third Unit Test',
                'type' => 'written',
                'month_offset' => -2, // 2 months ago
                'duration' => 90,
                'total_marks' => 50,
            ],
            [
                'name' => 'Annual Examination',
                'type' => 'written',
                'month_offset' => -1, // 1 month ago
                'duration' => 180,
                'total_marks' => 100,
            ],
        ];

        $examCounter = 1;

        // Create regular exams
        foreach ($examTypes as $examType) {
            foreach ($classes as $class) {
                foreach ($subjects as $subject) {
                    $examDate = now()->addMonths($examType['month_offset'])->startOfMonth()->addDays(15 + rand(0, 5));
                    $status = $examType['month_offset'] < -1 ? 'completed' : ($examType['month_offset'] == -1 ? 'ongoing' : 'scheduled');

                    // Generate start and end time
                    $startTime = '09:00:00';
                    $endTime = date('H:i:s', strtotime($startTime . ' + ' . $examType['duration'] . ' minutes'));

                    $examData = [
                        'exam_number' => 'EXM-2024-' . str_pad($examCounter++, 4, '0', STR_PAD_LEFT),
                        'name' => $examType['name'] . ' - ' . $subject->name . ' (Class ' . $class->name . ')',
                        'description' => 'This is a ' . strtolower($examType['name']) . ' for ' . $subject->name . ' subject.',
                        'class_id' => $class->id,
                        'subject_id' => $subject->id,
                        'exam_date' => $examDate->format('Y-m-d'),
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'duration_minutes' => $examType['duration'],
                        'total_marks' => $examType['total_marks'],
                        'pass_marks' => (int)($examType['total_marks'] * 0.35), // 35% passing
                        'exam_type' => $examType['type'],
                        'instructions' => $this->generateExamInstructions($examType['type']),
                        'status' => $status,
                        'created_by' => $adminUser->id,
                        'created_at' => $examDate->copy()->subDays(15),
                        'updated_at' => now(),
                    ];

                    // Insert exam
                    DB::table('exams')->insert($examData);
                }
            }
        }

        // Create some special exams
        $specialExams = [
            [
                'name' => 'Mathematics Olympiad',
                'type' => 'written',
                'class_ids' => [8, 9, 10],
                'subject' => 'Mathematics',
                'date_offset' => -3,
            ],
            [
                'name' => 'Science Quiz Competition',
                'type' => 'written',
                'class_ids' => [6, 7, 8],
                'subject' => 'Science',
                'date_offset' => -2,
            ],
            [
                'name' => 'English Essay Writing Contest',
                'type' => 'written',
                'class_ids' => [9, 10, 11, 12],
                'subject' => 'English',
                'date_offset' => -1,
            ],
        ];

        foreach ($specialExams as $specialExam) {
            foreach ($specialExam['class_ids'] as $classId) {
                $class = $classes->where('id', $classId)->first();
                if (!$class) continue;

                $examDate = now()->addMonths($specialExam['date_offset'])->startOfMonth()->addDays(20);
                $subject = $subjects->where('name', 'like', '%' . $specialExam['subject'] . '%')->first();
                if (!$subject) $subject = $subjects->first();

                $examData = [
                    'exam_number' => 'EXM-2024-' . str_pad($examCounter++, 4, '0', STR_PAD_LEFT),
                    'name' => $specialExam['name'] . ' - Class ' . $class->name,
                    'description' => 'Special ' . strtolower($specialExam['name']) . ' for Class ' . $class->name,
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'exam_date' => $examDate->format('Y-m-d'),
                    'start_time' => '10:00:00',
                    'end_time' => '12:00:00',
                    'duration_minutes' => 120,
                    'total_marks' => 100,
                    'pass_marks' => 50,
                    'exam_type' => $specialExam['type'],
                    'instructions' => 'Special ' . $specialExam['name'] . ' instructions apply.',
                    'status' => 'completed',
                    'created_by' => $adminUser->id,
                    'created_at' => $examDate->copy()->subDays(15),
                    'updated_at' => now(),
                ];

                DB::table('exams')->insert($examData);
            }
        }

        $this->command->info('✅ Exams seeded successfully!');
        $this->command->info('📊 Created exams for all classes and subjects');
    }

    /**
     * Generate exam instructions based on type
     */
    private function generateExamInstructions($examType): string
    {
        $instructions = [
            'written' => "1. Duration: varies by exam\n2. Answer all questions\n3. Use of calculator allowed for mathematics\n4. Write your roll number clearly\n5. Do not write anything on the question paper\n6. Follow all exam regulations",
            'oral' => "1. Be punctual for your scheduled time\n2. Prepare thoroughly\n3. Speak clearly and confidently\n4. Listen carefully to questions\n5. Take your time to think before answering",
            'practical' => "1. Follow all safety protocols\n2. Handle equipment carefully\n3. Record observations accurately\n4. Clean up your workspace\n5. Ask for help if needed",
            'online' => "1. Ensure stable internet connection\n2. Use a compatible browser\n3. Do not refresh the page unnecessarily\n4. Submit before the deadline\n5. Contact support for technical issues",
        ];

        return $instructions[$examType] ?? $instructions['written'];
    }
}
