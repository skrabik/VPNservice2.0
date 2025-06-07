<?php

namespace App\Orchid\Screens\Server;

use App\Models\Server;
use App\Orchid\Layouts\Server\ServerListLayout;
use Orchid\Screen\Screen;

class ServerScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'servers' => Server::all(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'ServerScreen';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    public function permission(): ?iterable
    {
        return [
            'platform.servers',
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
            ServerListLayout::class,
        ];
    }
}
