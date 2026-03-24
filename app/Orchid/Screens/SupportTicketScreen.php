<?php

namespace App\Orchid\Screens;

use App\Models\SupportTicket;
use Orchid\Screen\Actions\Link;
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

    public function description(): ?string
    {
        return 'Список обращений клиентов и ответов поддержки.';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.support-tickets',
        ];
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
                    ->filter()
                    ->render(fn (SupportTicket $ticket) => Link::make("#{$ticket->id}")
                        ->route('platform.support-tickets.view', $ticket)),

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

                TD::make('source_channel', 'Канал')
                    ->sort()
                    ->render(fn (SupportTicket $ticket) => $ticket->source_channel),

                TD::make('status', 'Статус')
                    ->sort()
                    ->render(fn (SupportTicket $ticket) => $ticket->status),

                TD::make('last_reply_at', 'Последний ответ')
                    ->sort()
                    ->render(fn (SupportTicket $ticket) => $ticket->last_reply_at?->format('d.m.Y H:i') ?? 'Нет'),

                TD::make('created_at', 'Создан')
                    ->sort()
                    ->render(function (SupportTicket $ticket) {
                        return $ticket->created_at->format('d.m.Y H:i');
                    }),

                TD::make(__('Actions'))
                    ->align(TD::ALIGN_CENTER)
                    ->width('120px')
                    ->render(fn (SupportTicket $ticket) => Link::make(__('Open'))
                        ->icon('bs.box-arrow-up-right')
                        ->route('platform.support-tickets.view', $ticket)),
            ]),
        ];
    }
}
