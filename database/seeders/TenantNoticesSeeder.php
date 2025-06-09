<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class TenantNoticesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Get admin and teachers who can create notices
        $adminUser = DB::table('users')->where('email', 'admin@school.com')->first();
        $teachers = DB::table('teachers')->get();
        $teacherUserIds = $teachers->pluck('user_id')->toArray();
        
        // Default admin if not found
        if (!$adminUser) {
            $adminUser = DB::table('users')->first();
        }
        
        // Combine admin and teacher user IDs
        $authorizedUserIds = array_merge([$adminUser->id], $teacherUserIds);

        // Notice types based on actual enum values
        $noticeTypes = ['general', 'academic', 'event', 'holiday', 'exam', 'fee', 'admission', 'sports', 'cultural', 'maintenance'];
        $priorities = ['low', 'medium', 'high', 'urgent'];
        $targetAudiences = ['all', 'students', 'teachers', 'parents', 'staff', 'specific_classes'];

        $notices = [];

        // Generate notices for the last 6 months
        for ($month = 5; $month >= 0; $month--) {
            $monthlyNotices = $faker->numberBetween(8, 15);
            
            for ($i = 0; $i < $monthlyNotices; $i++) {
                $type = $faker->randomElement($noticeTypes);
                $priority = $faker->randomElement($priorities);
                $isPublished = $month > 0 ? $faker->boolean(80) : $faker->boolean(50);
                $audience = $faker->randomElement($targetAudiences);
                
                $createdDate = $faker->dateTimeBetween("-$month months", "-$month months");
                $publishedAt = $isPublished ? $createdDate : null;
                $expiresAt = $publishedAt ? $faker->optional(0.7)->dateTimeBetween($publishedAt, "+2 months") : null;

                $notices[] = [
                    'title' => $this->generateNoticeTitle($type, $faker),
                    'content' => $this->generateNoticeContent($type, $faker),
                    'type' => $type,
                    'priority' => $priority,
                    'target_audience' => $audience,
                    'class_ids' => $audience === 'specific_classes' ? json_encode([1, 2, 3]) : null,
                    'is_published' => $isPublished,
                    'published_at' => $publishedAt,
                    'expires_at' => $expiresAt,
                    'is_urgent' => $priority === 'urgent',
                    'attachment_path' => $faker->optional(0.2)->word() . '.pdf',
                    'attachment_name' => $faker->optional(0.2)->sentence(3) . '.pdf',
                    'view_count' => $isPublished ? $faker->numberBetween(10, 200) : 0,
                    'created_by' => $faker->randomElement($authorizedUserIds),
                    'created_at' => $createdDate,
                    'updated_at' => $publishedAt ?? $createdDate,
                ];
            }
        }

        // Insert notices in batches
        foreach (array_chunk($notices, 50) as $chunk) {
            DB::table('notices')->insert($chunk);
        }

        $this->command->info('✅ Notices seeded successfully!');
        $this->command->info('📢 Created notices for various categories and priorities');
    }

    private function generateNoticeTitle($type, $faker): string
    {
        $templates = [
            'general' => ['Important General Notice', 'School Information Update', 'General Announcement'],
            'academic' => ['Academic Calendar Update', 'Subject Assignment Notice', 'Academic Year Information'],
            'event' => ['Upcoming School Event', 'Special Event Announcement', 'Event Participation Notice'],
            'holiday' => ['Holiday Declaration', 'School Closure Notice', 'Vacation Schedule'],
            'exam' => ['Examination Schedule', 'Exam Timetable Released', 'Test Announcement'],
            'fee' => ['Fee Payment Notice', 'Fee Due Reminder', 'Payment Schedule'],
            'admission' => ['Admission Notice', 'Enrollment Information', 'Admission Process Update'],
            'sports' => ['Sports Event Announcement', 'Athletic Competition', 'Sports Day Notice'],
            'cultural' => ['Cultural Event Notice', 'Arts Program Announcement', 'Cultural Competition'],
            'maintenance' => ['Maintenance Work Notice', 'Facility Update', 'Infrastructure Work'],
        ];

        return $faker->randomElement($templates[$type] ?? ['General Notice']);
    }

    private function generateNoticeContent($type, $faker): string
    {
        $content = "This is an important {$type} notice. ";
        $content .= $faker->sentence(10);
        $content .= " Please read carefully and follow the instructions provided.";
        
        return $content;
    }
}
