<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class TenantExpensesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Get admin and accountant users who can create expenses
        $adminUser = DB::table('users')->where('email', 'admin@esms.local')->first();
        $accountantUser = DB::table('users')->where('email', 'accountant@esms.local')->first();
        
        // Default to first user if specific roles not found
        if (!$adminUser) {
            $adminUser = DB::table('users')->first();
        }
        if (!$accountantUser) {
            $accountantUser = DB::table('users')->skip(1)->first() ?? $adminUser;
        }

        // Expense categories and typical amounts
        $expenseCategories = [
            'Infrastructure' => [
                'Building Maintenance' => [1000, 5000],
                'Classroom Renovation' => [2000, 8000],
                'Furniture Purchase' => [1500, 6000],
                'Electrical Work' => [800, 3000],
                'Plumbing Work' => [500, 2500],
            ],
            'Utilities' => [
                'Electricity Bill' => [800, 2000],
                'Water Bill' => [300, 800],
                'Internet Bill' => [500, 1200],
                'Telephone Bill' => [200, 600],
                'Gas Bill' => [150, 400],
            ],
            'Educational' => [
                'Books Purchase' => [2000, 8000],
                'Laboratory Equipment' => [3000, 15000],
                'Sports Equipment' => [1000, 5000],
                'Computer Hardware' => [5000, 25000],
                'Software License' => [2000, 10000],
            ],
            'Administrative' => [
                'Office Supplies' => [300, 1500],
                'Printing & Stationery' => [500, 2000],
                'Transportation' => [400, 1800],
                'Communication' => [200, 800],
                'Legal & Professional' => [1000, 5000],
            ],
            'Staff' => [
                'Training & Development' => [1500, 8000],
                'Staff Welfare' => [800, 3000],
                'Medical Allowance' => [500, 2500],
                'Bonus Payment' => [2000, 10000],
                'Refreshments' => [300, 1200],
            ],
            'Events' => [
                'Annual Function' => [5000, 20000],
                'Sports Day' => [2000, 8000],
                'Cultural Events' => [1500, 6000],
                'Field Trips' => [3000, 12000],
                'Parent Meeting' => [500, 2000],
            ]
        ];

        // Payment methods and their probability
        $paymentMethods = [
            'cash' => 30,
            'cheque' => 25,
            'online_transfer' => 20,
            'card' => 15,
            'upi' => 10
        ];

        $expenseRecords = [];
        $totalExpenses = 0;

        // Generate expenses for the last 12 months
        for ($month = 11; $month >= 0; $month--) {
            $monthlyExpenses = $faker->numberBetween(15, 25); // 15-25 expenses per month
            
            for ($i = 0; $i < $monthlyExpenses; $i++) {
                $category = $faker->randomElement(array_keys($expenseCategories));
                $subcategory = $faker->randomElement(array_keys($expenseCategories[$category]));
                $amountRange = $expenseCategories[$category][$subcategory];
                $amount = $faker->numberBetween($amountRange[0], $amountRange[1]);
                
                // Determine payment method based on probability
                $paymentMethod = $this->getWeightedRandomPaymentMethod($paymentMethods, $faker);
                
                // 95% approved, 3% pending, 2% rejected
                $statusRand = $faker->numberBetween(1, 100);                if ($statusRand <= 95) {
                    $status = 'approved';
                    $approvedBy = $adminUser ? $adminUser->id : null;
                    $approvedAt = $faker->dateTimeBetween("-$month months", "-$month months");
                } elseif ($statusRand <= 98) {
                    $status = 'pending';
                    $approvedBy = null;
                    $approvedAt = null;
                } else {
                    $status = 'rejected';
                    $approvedBy = $adminUser ? $adminUser->id : null;
                    $approvedAt = $faker->dateTimeBetween("-$month months", "-$month months");
                }

                $createdBy = $faker->randomElement([$adminUser, $accountantUser]);
                $expenseDate = $faker->dateTimeBetween("-$month months", "-$month months");                $expenseRecords[] = [
                    'expense_number' => 'EXP-' . $expenseDate->format('Y') . '-' . $faker->numerify('####'),
                    'title' => $subcategory,
                    'category' => $category,
                    'description' => $this->generateDescription($category, $subcategory, $faker),
                    'amount' => $amount,
                    'expense_date' => $expenseDate,
                    'payment_method' => $paymentMethod,
                    'receipt_number' => $status === 'approved' ? 'RCP-' . $faker->numerify('####') : null,
                    'status' => $status,
                    'created_by' => $createdBy->id,
                    'approved_by' => $approvedBy,
                    'approved_at' => $approvedAt,
                    'notes' => $this->generateRemarks($status, $faker),
                    'custom_fields' => json_encode(['recurring' => $faker->boolean(20), 'vendor' => $this->generateVendorName($category, $faker)]),
                    'created_at' => $expenseDate,
                    'updated_at' => $approvedAt ?? $expenseDate,
                ];

                if ($status === 'approved') {
                    $totalExpenses += $amount;
                }
            }
        }        // Insert all expense records
        foreach (array_chunk($expenseRecords, 100) as $chunk) {
            DB::table('expenses')->insert($chunk);
        }

        // Create some high-value expenses that require special approval
        $specialExpenses = [
            [
                'title' => 'New Computer Lab Setup',
                'category' => 'Educational',
                'amount' => 150000,
                'description' => 'Complete setup of new computer lab with 30 computers, networking, and software installation.',
            ],
            [
                'title' => 'School Bus Purchase',
                'category' => 'Infrastructure',
                'amount' => 800000,
                'description' => 'Purchase of new school bus for student transportation.',
            ],
            [
                'title' => 'Library Renovation',
                'category' => 'Infrastructure',
                'amount' => 75000,
                'description' => 'Complete renovation of school library including new furniture and shelving.',
            ],
            [
                'title' => 'Annual Insurance Premium',
                'category' => 'Administrative',
                'amount' => 45000,
                'description' => 'Annual insurance premium for school building and equipment coverage.',
            ],
        ];        foreach ($specialExpenses as $specialExpense) {
            $expenseDate = $faker->dateTimeBetween('-6 months', '-1 month');
            DB::table('expenses')->insert([
                'expense_number' => 'EXP-' . $expenseDate->format('Y') . '-' . $faker->numerify('####'),
                'title' => $specialExpense['title'],
                'category' => $specialExpense['category'],
                'description' => $specialExpense['description'],
                'amount' => $specialExpense['amount'],
                'expense_date' => $expenseDate,
                'payment_method' => 'cheque',
                'receipt_number' => 'RCP-' . $faker->numerify('####'),
                'status' => 'approved',
                'created_by' => $accountantUser ? $accountantUser->id : $adminUser->id,
                'approved_by' => $adminUser ? $adminUser->id : null,
                'approved_at' => $faker->dateTimeBetween('-5 months', '-1 month'),
                'notes' => 'High-value expense approved by management',
                'custom_fields' => json_encode(['vendor' => $faker->company, 'invoice' => 'INV-' . $faker->numerify('####')]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✅ Expenses seeded successfully!');
        $this->command->info("💰 Total approved expenses: ₹" . number_format($totalExpenses));
    }

    /**
     * Get weighted random payment method
     */
    private function getWeightedRandomPaymentMethod($methods, $faker)
    {
        $rand = $faker->numberBetween(1, 100);
        $sum = 0;
        
        foreach ($methods as $method => $weight) {
            $sum += $weight;
            if ($rand <= $sum) {
                return $method;
            }
        }
        
        return 'cash'; // fallback
    }

    /**
     * Generate description based on category
     */
    private function generateDescription($category, $subcategory, $faker)
    {
        $descriptions = [
            'Infrastructure' => [
                'Building Maintenance' => 'Routine maintenance and repair work for school building infrastructure',
                'Classroom Renovation' => 'Renovation and improvement of classroom facilities',
                'Furniture Purchase' => 'Purchase of new furniture for classrooms and offices',
                'Electrical Work' => 'Electrical maintenance and installation work',
                'Plumbing Work' => 'Plumbing repairs and maintenance services',
            ],
            'Utilities' => [
                'Electricity Bill' => 'Monthly electricity consumption charges',
                'Water Bill' => 'Monthly water supply and sewage charges',
                'Internet Bill' => 'Monthly internet and WiFi service charges',
                'Telephone Bill' => 'Monthly telephone and communication charges',
                'Gas Bill' => 'Monthly LPG and cooking gas expenses',
            ],
            'Educational' => [
                'Books Purchase' => 'Purchase of textbooks and reference materials for library',
                'Laboratory Equipment' => 'Scientific instruments and laboratory equipment',
                'Sports Equipment' => 'Sports goods and equipment for physical education',
                'Computer Hardware' => 'Computer systems and IT equipment purchase',
                'Software License' => 'Educational software licenses and subscriptions',
            ]
        ];

        return $descriptions[$category][$subcategory] ?? 
               $subcategory . ' - ' . $faker->sentence(8);
    }

    /**
     * Generate vendor name based on category
     */
    private function generateVendorName($category, $faker)
    {
        $vendors = [
            'Infrastructure' => ['BuildTech Solutions', 'Reliable Contractors', 'Metro Construction', 'Prime Builders'],
            'Utilities' => ['State Electricity Board', 'Municipal Corporation', 'Airtel Business', 'BSNL'],
            'Educational' => ['Academic Publishers', 'EduTech Solutions', 'School Supplies Co.', 'Learning Resources'],
            'Administrative' => ['Office Mart', 'Business Solutions', 'Admin Services', 'Corporate Supplies'],
            'Staff' => ['HR Consultants', 'Training Institute', 'Wellness Center', 'Professional Services'],
            'Events' => ['Event Managers', 'Catering Services', 'Decoration House', 'Audio Visual Solutions'],
        ];

        return $vendors[$category][array_rand($vendors[$category])] ?? $faker->company;
    }

    /**
     * Generate remarks based on status
     */
    private function generateRemarks($status, $faker)
    {
        $remarks = [
            'approved' => [
                'Expense approved and processed',
                'Payment completed successfully',
                'All documentation verified and approved',
                'Approved as per budget allocation',
                'Expense justified and approved',
            ],
            'pending' => [
                'Awaiting management approval',
                'Documentation under review',
                'Pending budget verification',
                'Approval in progress',
                'Under administrative review',
            ],
            'rejected' => [
                'Insufficient budget allocation',
                'Documentation incomplete',
                'Not aligned with current priorities',
                'Requires additional justification',
                'Expense not approved by management',
            ]
        ];

        return $remarks[$status][array_rand($remarks[$status])];
    }
}
