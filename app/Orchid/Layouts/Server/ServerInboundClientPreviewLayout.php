<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Server;

use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ServerInboundClientPreviewLayout extends Table
{
    protected $target = 'inbound_clients_preview';

    protected function columns(): array
    {
        return [
            TD::make('inbound_remark', 'Inbound')->render(
                fn (array $client) => $client['inbound_remark'] ?? '-'
            ),
            TD::make('email', 'Email')->render(
                fn (array $client) => $client['email'] ?? '-'
            ),
            TD::make('uuid', 'UUID')->render(
                fn (array $client) => $client['uuid'] ?? '-'
            ),
            TD::make('sub_id', 'Sub ID')->render(
                fn (array $client) => $client['sub_id'] ?? '-'
            ),
            TD::make('traffic_limit_bytes', 'Limit')->render(
                fn (array $client) => ($client['traffic_limit_bytes'] ?? null) === null ? '-' : (string) $client['traffic_limit_bytes']
            ),
            TD::make('traffic_used_bytes', 'Used')->render(
                fn (array $client) => (string) ($client['traffic_used_bytes'] ?? 0)
            ),
            TD::make('is_enabled', 'Enabled')->render(
                fn (array $client) => ! empty($client['is_enabled']) ? 'Yes' : 'No'
            ),
            TD::make('last_online_at', 'Last Online')->render(
                fn (array $client) => $client['last_online_at'] ?: '-'
            ),
        ];
    }
}
