<?php

namespace App\Orchid\Screens\Server;

use Orchid\Screen\Screen;
use App\Orchid\Layouts\Server\ServerEditLayout;
use App\Models\Server;

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
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Server Edit';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.systems.servers',
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
            ServerEditLayout::class,
        ];
    }
}
