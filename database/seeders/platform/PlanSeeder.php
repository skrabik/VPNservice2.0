<?php

namespace Database\Seeders\platform;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'title' => 'Basic',
                'slug' => 'basic',
                'description' => 'Basic VPN plan',
                'price' => 5.99,
                'period' => 30,
                'active' => true,
            ],
            [
                'title' => 'Standard',
                'slug' => 'standard',
                'description' => 'Standard VPN plan',
                'price' => 9.99,
                'period' => 30,
                'active' => true,
            ],
            [
                'title' => 'Premium',
                'slug' => 'premium',
                'description' => 'Premium VPN plan',
                'price' => 19.99,
                'period' => 30,
                'active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::create($plan);
        }
    }
}
