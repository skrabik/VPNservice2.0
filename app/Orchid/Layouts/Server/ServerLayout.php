<?php

namespace App\Orchid\Layouts\Server;

use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ServerLayout extends Table
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
            TD::make('id', 'ID'),
            TD::make('hostname', 'Hostname'),
            TD::make('ip_address', 'IP Address'),
            TD::make('location', 'Location'),
            TD::make('active', 'Status'),
            TD::make('created_at', 'Created'),
        ];
    }
}
