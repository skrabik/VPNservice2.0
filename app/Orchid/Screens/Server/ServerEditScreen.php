<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Server;

use App\Models\Server;
use App\Orchid\Layouts\Server\ServerEditLayout;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ServerEditScreen extends Screen
{
    /**
     * @var Server
     */
    public $server;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Server $server): iterable
    {
        return [
            'server' => $server,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return $this->server->exists ? 'Edit Server' : 'Create Server';
    }

    public function description(): ?string
    {
        return 'Server Information';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.servers',
        ];
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Delete')
                ->icon('bs.trash3')
                ->confirm('Are you sure you want to delete this server?')
                ->method('remove')
                ->canSee($this->server->exists),

            Button::make('Save')
                ->icon('bs.check-circle')
                ->method('save'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::block(ServerEditLayout::class)
                ->title('Server Information')
                ->description('Update server information.')
                ->commands(
                    Button::make('Save')
                        ->type(Color::BASIC)
                        ->icon('bs.check-circle')
                        ->method('save')
                ),
        ];
    }

    public function save(Server $server, Request $request)
    {
        $server->fill($request->get('server'))->save();
        Toast::info('Server was saved.');

        return redirect()->route('platform.servers');
    }

    public function remove(Server $server)
    {
        $server->delete();
        Toast::info('Server was removed');

        return redirect()->route('platform.servers');
    }
}
