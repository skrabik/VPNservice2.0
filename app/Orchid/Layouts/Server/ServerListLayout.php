<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Server;

use App\Models\Server;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ServerListLayout extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'servers';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): array
    {
        return [
            TD::make('id', 'ID')->sort()->filter(),
            TD::make('hostname', 'Host name')->sort()->filter(),
            TD::make('type', 'Type')->sort()
                ->render(function (Server $server) {
                    return Server::SERVER_TYPE_OPTIONS[$server->type] ?? '';
                })
                ->filter(),
            TD::make('ip_address', 'IP')->sort()->filter(),
            TD::make('active', 'Active')->render(fn (Server $server) => $server->active ? 'Yes' : 'No'),
            TD::make('created_at', 'Created At')->render(fn (Server $server) => optional($server->created_at)->format('d.m.Y H:i')),
            TD::make('Actions')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn (Server $server) => Link::make('Edit')
                    ->icon('bs.pencil')
                    ->route('platform.servers.edit', $server)),
        ];
    }
}
