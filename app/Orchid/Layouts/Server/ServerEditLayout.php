<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Server;

use App\Models\Server;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Input;
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
            Input::make('server.type')
                ->type('hidden')
                ->value(Server::SERVER_TYPE_3XUI_KEY),

            Input::make('server.hostname')
                ->type('text')
                ->max(255)
                ->required()
                ->title('Host Name')
                ->placeholder('Enter server host name'),

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

            Input::make('server.max_users')
                ->type('number')
                ->min(1)
                ->title('Max Users')
                ->placeholder('Optional capacity limit'),

            CheckBox::make('server.active')
                ->sendTrueOrFalse()
                ->title('Active')
                ->help('Enable or disable server'),
        ];
    }
}
