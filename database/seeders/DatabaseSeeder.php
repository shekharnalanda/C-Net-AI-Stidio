<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free Trial',
                'slug' => 'trial',
                'price' => 0,
                'billing_cycle' => 'trial',
                'limits' => [
                    'days' => 7,
                    'projects' => 10,
                ],
                'features' => [
                    'Studio Preview',
                    'Project Workspace',
                    'Basic Export',
                ],
            ],
            [
                'name' => 'Creator',
                'slug' => 'creator',
                'price' => 299,
                'billing_cycle' => 'monthly',
                'limits' => [
                    'projects' => 100,
                ],
                'features' => [
                    'Video Studio',
                    'Image Studio',
                    'AI Tools',
                    'HD Export',
                ],
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'price' => 699,
                'billing_cycle' => 'monthly',
                'limits' => [
                    'projects' => 500,
                ],
                'features' => [
                    'Advanced AI Studio',
                    'Business Ads',
                    'Premium Templates',
                    'Priority Rendering',
                ],
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'price' => 1499,
                'billing_cycle' => 'monthly',
                'limits' => [
                    'projects' => -1,
                ],
                'features' => [
                    'Commercial Use',
                    'Brand Workspace',
                    'Team Ready',
                    'High Priority Rendering',
                ],
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                array_merge($plan, ['is_active' => true])
            );
        }
    }
}
