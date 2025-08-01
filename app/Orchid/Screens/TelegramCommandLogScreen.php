<?php

namespace App\Orchid\Screens;

use App\Models\TelegramCommandLog;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class TelegramCommandLogScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $count_data = TelegramCommandLog::query()
            ->groupBy('action')
            ->select('action', DB::raw('COUNT(*) as count'))
            ->get();

        return [
            'count_data' => $count_data,
            'telegram_command_logs' => TelegramCommandLog::with('customer')
                ->latest()
                ->paginate(25),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Логи команд Telegram';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'История всех команд, выполненных в Telegram боте';
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

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::block(Layout::view('layouts.stats')),
            Layout::table('telegram_command_logs', [
                TD::make('id', 'ID'),
                TD::make('customer_id', 'ID клиента'),
                TD::make('command_name', 'Команда'),
                TD::make('action', 'Действие'),
                TD::make('created_at', 'Дата действия'),
            ]),
        ];
    }
}
