<?php

namespace Database\Seeders\platform;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = Plan::all();
        Customer::factory()
            ->count(50)
            ->create()
            ->each(function (Customer $customer) use ($plans) {
                $count = rand(0, 3);
                for ($i = 0; $i < $count; $i++) {
                    $plan = $plans->random();
                    $dateStart = now()->subDays(rand(0, 365));
                    $dateEnd = (clone $dateStart)->addDays(rand(7, 90));
                    Subscription::create([
                        'customer_id' => $customer->id,
                        'plan_id' => $plan->id,
                        'date_start' => $dateStart,
                        'date_end' => $dateEnd,
                    ]);
                }
            });
    }
}
