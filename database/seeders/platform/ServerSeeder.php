<?php

namespace Database\Seeders\platform;

use App\Models\Server;
use Illuminate\Database\Seeder;

class ServerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $servers = [
            [
                'hostname' => 'vpn-server-1',
                'ip_address' => '192.168.1.100',
                'location' => 'New York',
                'active' => true,
                'max_users' => 1000,
            ],
            [
                'hostname' => 'vpn-server-2',
                'ip_address' => '192.168.1.101',
                'location' => 'London',
                'active' => true,
                'max_users' => 800,
            ],
            [
                'hostname' => 'vpn-server-3',
                'ip_address' => '192.168.1.102',
                'location' => 'Tokyo',
                'active' => true,
                'max_users' => 500,
            ],
            [
                'hostname' => 'vpn-server-4',
                'ip_address' => '192.168.1.103',
                'location' => 'Singapore',
                'active' => false,
                'max_users' => 300,
            ],
            [
                'hostname' => 'vpn-server-5',
                'ip_address' => '192.168.1.104',
                'location' => 'Frankfurt',
                'active' => true,
                'max_users' => 600,
            ],
        ];

        foreach ($servers as $server) {
            Server::create($server);
        }
    }
}
