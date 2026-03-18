<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Server;

use App\Models\ServerInbound;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ServerInboundListLayout extends Table
{
    protected $target = 'server_inbounds';

    protected function columns(): array
    {
        return [
            TD::make('remark', 'Remark')->render(fn (ServerInbound $inbound) => $inbound->remark ?: '-'),
            TD::make('protocol', 'Protocol')->render(fn (ServerInbound $inbound) => strtoupper((string) $inbound->protocol)),
            TD::make('port', 'Port'),
            TD::make('tag', 'Tag')->render(fn (ServerInbound $inbound) => $inbound->tag ?: '-'),
            TD::make('is_enabled', 'Enabled')->render(fn (ServerInbound $inbound) => $inbound->is_enabled ? 'Yes' : 'No'),
            TD::make('is_default', 'Default')->render(fn (ServerInbound $inbound) => $inbound->is_default ? 'Yes' : 'No'),
            TD::make('clients', 'Clients')->render(function (ServerInbound $inbound) {
                $payload = $inbound->decodeRawPayload();

                return count($payload['clientStats'] ?? []);
            }),
            TD::make('last_synced_at', 'Last Sync')->render(
                fn (ServerInbound $inbound) => optional($inbound->last_synced_at)->format('d.m.Y H:i') ?: '-'
            ),
            TD::make('actions', 'Actions')->render(function (ServerInbound $inbound) {
                return Button::make($inbound->is_default ? 'Default' : 'Set Default')
                    ->icon('bs.pin-angle')
                    ->method('setDefaultInbound')
                    ->novalidate()
                    ->parameters([
                        'server_inbound_id' => $inbound->id,
                    ]);
            }),
        ];
    }
}
