<?php

namespace App\Orchid\Screens\AdminNotifications;

use App\Models\AdminNotification;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class AdminNotificationScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'notifications' => AdminNotification::all(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Admin Notifications';
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
            'platform.notifications',
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
            Layout::view('layouts.AdminNotification.table'),
        ];
    }
}
