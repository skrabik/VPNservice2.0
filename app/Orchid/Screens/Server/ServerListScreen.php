<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Server;

use App\Models\Server;
use App\Orchid\Layouts\Server\ServerListLayout;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;

class ServerListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'servers' => Server::defaultSort('id', 'desc')->paginate(),
        ];
    }

    public function name(): ?string
    {
        return 'Servers';
    }

    public function description(): ?string
    {
        return 'List of all VPN servers';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.servers',
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Add')
                ->icon('bs.plus-circle')
                ->route('platform.servers.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            ServerListLayout::class,
        ];
    }
}
