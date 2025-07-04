<?php

namespace Database\Seeders\platform;

use App\Models\AdminNotification;
use Illuminate\Database\Seeder;

class AdminNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AdminNotification::factory()
            ->count(15)
            ->create();
    }
}
