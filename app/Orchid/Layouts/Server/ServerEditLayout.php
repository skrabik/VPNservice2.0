<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Server;

use App\Models\Server;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Checkbox;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class ServerEditLayout extends Rows
{
    /**
     * Used to create the title of a group of form elements.
     *
     * @var string|null
     */
    protected $title;

    /**
     * Get the fields elements to be displayed.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Input::make('server.hostname')
                ->type('text')
                ->max(255)
                ->required()
                ->title('Name')
                ->placeholder('Enter server host name'),

            Select::make('server.type')
                ->options(Server::SERVER_TYPE_OPTIONS)
                ->required()
                ->title('Type')
                ->help('Select server type'),

            Input::make('server.ip_address')
                ->type('text')
                ->max(255)
                ->required()
                ->title('IP')
                ->placeholder('Enter server IP address'),

            Input::make('server.location')
                ->type('text')
                ->max(255)
                ->title('Location')
                ->placeholder('Enter server location'),

            Checkbox::make('server.active')
                ->sendTrueOrFalse()
                ->title('Active')
                ->help('Enable or disable server'),
        ];
    }
}
