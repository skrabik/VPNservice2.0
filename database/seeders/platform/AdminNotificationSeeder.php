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
        AdminNotification::create([
            'message' => json_encode([
                'type' => 'notification',
                'info' => 'test notification',
                'data' => now(),
            ]),
        ]);
    }
}
