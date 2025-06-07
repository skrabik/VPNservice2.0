<?php

namespace Database\Seeders;

use Database\Seeders\Platform\AdminNotificationSeeder;
use Database\Seeders\Platform\CustomerSeeder;
use Database\Seeders\Platform\PaymentMethodSeeder;
use Database\Seeders\Platform\PlanSeeder;
use Database\Seeders\Platform\ServerSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ServerSeeder::class,
            PlanSeeder::class,
            PaymentMethodSeeder::class,
            AdminNotificationSeeder::class,
            CustomerSeeder::class,
        ]);
    }
}
