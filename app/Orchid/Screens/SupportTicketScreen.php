<?php

namespace App\Orchid\Screens;

use App\Models\SupportTicket;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class SupportTicketScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     */
    public function query(): array
    {
        return [
            'tickets' => SupportTicket::with('customer')
                ->orderBy('created_at', 'desc')
                ->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Тикеты поддержки';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): array
    {
        return [
            Layout::table('tickets', [
                TD::make('id', 'ID')
                    ->sort()
                    ->filter(),

                TD::make('customer.first_name', 'Customer Name')
                    ->sort()
                    ->filter(),

                TD::make('customer.id', 'Customer ID')
                    ->sort()
                    ->filter(),

                TD::make('customer.telegram_id', 'Telegram ID')
                    ->sort()
                    ->filter(),

                TD::make('message', 'Сообщение')
                    ->width(300)
                    ->render(function (SupportTicket $ticket) {
                        return substr($ticket->message, 0, 100).(strlen($ticket->message) > 100 ? '...' : '');
                    }),

                TD::make('created_at', 'Создан')
                    ->sort()
                    ->render(function (SupportTicket $ticket) {
                        return $ticket->created_at->format('d.m.Y H:i');
                    }),
            ]),
        ];
    }
}
