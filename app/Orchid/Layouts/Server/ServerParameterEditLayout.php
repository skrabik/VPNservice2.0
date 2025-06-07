<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Server;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class ServerParameterEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('parameter.key')
                ->type('text')
                ->max(255)
                ->required()
                ->title('Key')
                ->placeholder('Enter parameter key'),

            Input::make('parameter.value')
                ->type('text')
                ->max(255)
                ->title('Value')
                ->placeholder('Enter parameter value'),
        ];
    }
}
