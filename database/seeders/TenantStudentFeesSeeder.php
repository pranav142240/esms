<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class TenantStudentFeesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Get all students from the students table
        $students = DB::table('students')->get();
        $classes = DB::table('classes')->get();

        // Fee types and amounts by class
        $feeStructure = [
            1 => ['tuition' => 2000, 'transport' => 1000, 'library' => 200, 'sports' => 300, 'exam' => 500],
            2 => ['tuition' => 2200, 'transport' => 1000, 'library' => 200, 'sports' => 300, 'exam' => 500],
            3 => ['tuition' => 2400, 'transport' => 1100, 'library' => 250, 'sports' => 350, 'exam' => 600],
            4 => ['tuition' => 2600, 'transport' => 1100, 'library' => 250, 'sports' => 350, 'exam' => 600],
            5 => ['tuition' => 2800, 'transport' => 1200, 'library' => 300, 'sports' => 400, 'exam' => 700],
            6 => ['tuition' => 3000, 'transport' => 1200, 'library' => 300, 'sports' => 400, 'exam' => 700],
            7 => ['tuition' => 3200, 'transport' => 1300, 'library' => 350, 'sports' => 450, 'exam' => 800],
            8 => ['tuition' => 3500, 'transport' => 1300, 'library' => 350, 'sports' => 450, 'exam' => 800],
            9 => ['tuition' => 4000, 'transport' => 1400, 'library' => 400, 'sports' => 500, 'exam' => 900],
            10 => ['tuition' => 4500, 'transport' => 1400, 'library' => 400, 'sports' => 500, 'exam' => 900],
            11 => ['tuition' => 5000, 'transport' => 1500, 'library' => 500, 'sports' => 600, 'exam' => 1000],
            12 => ['tuition' => 5500, 'transport' => 1500, 'library' => 500, 'sports' => 600, 'exam' => 1000],
        ];

        // Academic months
        $academicMonths = [
            'April', 'May', 'June', 'July', 'August', 'September',
            'October', 'November', 'December', 'January', 'February', 'March'
        ];        foreach ($students as $student) {
            $classLevel = $student->class_id ?? 1;
            $fees = $feeStructure[$classLevel] ?? $feeStructure[1];

            foreach ($academicMonths as $index => $month) {
                foreach ($fees as $feeType => $amount) {
                    // Determine payment status (80% paid, 15% pending, 5% overdue)
                    $statusRand = $faker->numberBetween(1, 100);
                    if ($statusRand <= 80) {
                        $status = 'paid';
                        $paidAmount = $amount;
                        $paidDate = $faker->dateTimeBetween("-" . (11 - $index) . " months", "-" . (10 - $index) . " months");
                        $paymentMethod = $faker->randomElement(['cash', 'online', 'cheque', 'card']);
                        $transactionId = $paymentMethod === 'online' ? 'TXN' . $faker->unique()->numerify('##########') : null;
                    } elseif ($statusRand <= 95) {
                        $status = 'pending';
                        $paidAmount = 0;
                        $paidDate = null;
                        $paymentMethod = null;
                        $transactionId = null;
                    } else {
                        $status = 'overdue';
                        $paidAmount = 0;
                        $paidDate = null;
                        $paymentMethod = null;
                        $transactionId = null;
                    }

                    // Due date is typically 10th of each month
                    $dueDate = now()->subMonths(11 - $index)->startOfMonth()->addDays(9);

                    DB::table('student_fees')->insert([
                        'student_id' => $student->id,
                        'class_id' => $classLevel,
                        'fee_type' => ucfirst($feeType) . ' Fee',
                        'fee_category' => $this->getFeeCategory($feeType),
                        'amount' => $amount,
                        'paid_amount' => $paidAmount,
                        'balance_amount' => $amount - $paidAmount,
                        'due_date' => $dueDate,
                        'paid_date' => $paidDate,
                        'status' => $status,
                        'payment_method' => $paymentMethod,
                        'transaction_id' => $transactionId,
                        'month' => $month,
                        'academic_year' => '2023-24',
                        'late_fee' => $status === 'overdue' ? $faker->numberBetween(50, 200) : 0,
                        'discount' => $faker->optional(0.1)->numberBetween(100, 500) ?? 0,
                        'remarks' => $status === 'overdue' ? 'Payment overdue' : ($status === 'pending' ? 'Payment pending' : 'Payment completed'),
                        'receipt_number' => $status === 'paid' ? 'RCP' . $faker->unique()->numerify('######') : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Create some additional fee records for special cases
        $specialFees = [
            ['name' => 'Annual Function Fee', 'amount' => 500, 'category' => 'Event'],
            ['name' => 'Science Lab Fee', 'amount' => 800, 'category' => 'Lab'],
            ['name' => 'Computer Lab Fee', 'amount' => 600, 'category' => 'Lab'],
            ['name' => 'Annual Sports Fee', 'amount' => 400, 'category' => 'Sports'],
            ['name' => 'Field Trip Fee', 'amount' => 300, 'category' => 'Activity'],
        ];        foreach ($students->take(8) as $student) { // Apply special fees to first 8 students
            foreach ($specialFees as $specialFee) {
                $isPaid = $faker->boolean(70); // 70% chance of being paid

                DB::table('student_fees')->insert([
                    'student_id' => $student->id,
                    'class_id' => $student->class_id ?? 1,
                    'fee_type' => $specialFee['name'],
                    'fee_category' => $specialFee['category'],
                    'amount' => $specialFee['amount'],
                    'paid_amount' => $isPaid ? $specialFee['amount'] : 0,
                    'balance_amount' => $isPaid ? 0 : $specialFee['amount'],
                    'due_date' => $faker->dateTimeBetween('-2 months', '+1 month'),
                    'paid_date' => $isPaid ? $faker->dateTimeBetween('-2 months', 'now') : null,
                    'status' => $isPaid ? 'paid' : 'pending',
                    'payment_method' => $isPaid ? $faker->randomElement(['cash', 'online', 'cheque']) : null,
                    'transaction_id' => $isPaid && $faker->boolean(50) ? 'TXN' . $faker->numerify('##########') : null,
                    'month' => 'Special',
                    'academic_year' => '2023-24',
                    'late_fee' => 0,
                    'discount' => 0,
                    'remarks' => $specialFee['name'] . ' for academic year 2023-24',
                    'receipt_number' => $isPaid ? 'RCP' . $faker->numerify('######') : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('✅ Student fees seeded successfully!');
    }

    /**
     * Get fee category based on fee type
     */
    private function getFeeCategory($feeType): string
    {
        $categories = [
            'tuition' => 'Academic',
            'transport' => 'Transport',
            'library' => 'Library',
            'sports' => 'Sports',
            'exam' => 'Examination',
        ];

        return $categories[$feeType] ?? 'Miscellaneous';
    }
}
