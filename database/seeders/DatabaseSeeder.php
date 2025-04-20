<?php

namespace Database\Seeders;

use Database\Seeders\Platform\AdminNotificationSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminNotificationSeeder::class,
        ]);
    }
}
