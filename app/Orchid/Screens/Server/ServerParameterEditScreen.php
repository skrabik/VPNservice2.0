<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Server;

use App\Models\Server;
use App\Models\ServerParameter;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ServerParameterEditScreen extends Screen
{
    public ?Server $server = null;

    public ?ServerParameter $parameter = null;

    public function query(Server $server, ?int $parameter = null): array
    {
        return [
            'server' => $server,
            'parameter' => $parameter ? ServerParameter::find($parameter) : new ServerParameter(['server_id' => $server->id]),
        ];
    }

    public function name(): ?string
    {
        return $this->parameter->exists ? 'Edit Parameter' : 'Create Parameter';
    }

    public function description(): ?string
    {
        return 'Server parameter details';
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
            Button::make('Delete')
                ->icon('bs.trash')
                ->confirm('Are you sure you want to delete this parameter?')
                ->method('remove')
                ->canSee($this->parameter->exists),

            Button::make('Save')
                ->type(Color::BASIC)
                ->icon('bs.check-circle')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::block([
                Layout::rows([
                    \Orchid\Screen\Fields\Input::make('parameter.key')
                        ->type('text')
                        ->max(255)
                        ->required()
                        ->title('Key')
                        ->placeholder('Enter parameter key'),

                    \Orchid\Screen\Fields\Input::make('parameter.value')
                        ->type('text')
                        ->max(255)
                        ->title('Value')
                        ->placeholder('Enter parameter value'),
                ]),
            ])
                ->title('Parameter Information')
                ->description('Update parameter information'),
        ];
    }

    public function save(Request $request)
    {
        $this->parameter->fill($request->get('parameter'))->save();
        Toast::info('Parameter was saved.');

        return redirect()->route('platform.servers.edit', $this->server);
    }

    public function remove()
    {
        $this->parameter->delete();
        Toast::info('Parameter was removed.');

        return redirect()->route('platform.servers.edit', $this->server);
    }
}
