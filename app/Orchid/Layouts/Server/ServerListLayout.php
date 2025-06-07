<?php

namespace App\Orchid\Layouts\Server;

use App\Models\Server;
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
    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')
                ->sort(),
            TD::make('hostname', 'Hostname')
                ->sort(),
            TD::make('ip_address', 'IP Address'),
            TD::make('location', 'Location'),
            TD::make('active', 'Status')
                ->render(fn (Server $server) => $server->active ? '✔️' : '❌')
                ->sort(),
            TD::make('created_at', 'Created')
                ->sort(),
        ];
    }
}
