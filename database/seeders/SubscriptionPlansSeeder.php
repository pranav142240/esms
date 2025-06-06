<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic Plan',
                'description' => 'Perfect for small schools with basic requirements',
                'price' => 29.00,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'features' => [
                    'Up to 50 users',
                    'Basic user management',
                    'Basic reporting',
                    'Email support',
                    '1GB storage',
                    'Student information system',
                    'Basic dashboard'
                ],
                'user_limit' => 50,
                'storage_limit_gb' => 1,
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Pro Plan',
                'description' => 'Ideal for medium-sized schools with advanced features',
                'price' => 79.00,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'features' => [
                    'Up to 200 users',
                    'Advanced user management',
                    'Advanced reporting & analytics',
                    'Priority email support',
                    '10GB storage',
                    'Custom roles & permissions',
                    'API access',
                    'Student performance tracking',
                    'Parent portal',
                    'Teacher dashboard'
                ],
                'user_limit' => 200,
                'storage_limit_gb' => 10,
                'is_active' => true,
                'is_default' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Enterprise Plan',
                'description' => 'Comprehensive solution for large educational institutions',
                'price' => 199.00,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'features' => [
                    'Unlimited users',
                    'Full feature access',
                    'Priority phone & email support',
                    '100GB storage',
                    'Advanced integrations',
                    'Custom branding',
                    'Dedicated support',
                    'Advanced analytics',
                    'Multi-campus support',
                    'White-label solution',
                    'Data export tools',
                    'Custom development'
                ],
                'user_limit' => null, // Unlimited
                'storage_limit_gb' => 100,
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Custom Plan',
                'description' => 'Tailored solution for specific institutional needs',
                'price' => 0.00, // Custom pricing
                'currency' => 'USD',
                'billing_cycle' => 'yearly',
                'features' => [
                    'Customizable feature set',
                    'Flexible user limits',
                    'Custom storage allocation',
                    'Dedicated account manager',
                    'Priority support',
                    'Custom integrations',
                    'Training and onboarding',
                    'SLA guarantees'
                ],
                'user_limit' => null,
                'storage_limit_gb' => null,
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 4,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::create($plan);
        }
    }
}
