<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Server;

use App\Models\ServerParameter;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ServerParameterLayout extends Table
{
    protected $target = 'parameters';

    protected function columns(): array
    {
        return [
            TD::make('key', 'Key')
                ->sort()
                ->filter(),

            TD::make('value', 'Value')
                ->sort()
                ->filter(),

            TD::make('created_at', 'Created')
                ->sort(),

            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn (ServerParameter $parameter) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        Link::make(__('Edit'))
                            ->route('platform.servers.parameters.edit', [
                                'server' => $parameter->server_id,
                                'parameter' => $parameter->id,
                            ])
                            ->icon('bs.pencil'),

                        Button::make(__('Delete'))
                            ->icon('bs.trash')
                            ->confirm(__('Are you sure you want to delete this parameter?'))
                            ->method('removeParameter', [
                                'id' => $parameter->id,
                            ]),
                    ])),
        ];
    }
}
