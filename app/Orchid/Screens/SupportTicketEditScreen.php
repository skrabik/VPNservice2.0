<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\SupportTicket;
use App\Orchid\Layouts\Support\SupportTicketReplyLayout;
use App\Services\CustomerSupportService;
use App\Services\SupportTicketReplyDeliveryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class SupportTicketEditScreen extends Screen
{
    /** @var SupportTicket */
    public $supportTicket;

    public function query(SupportTicket $supportTicket): iterable
    {
        $supportTicket->load([
            'assignedUser',
            'customer',
            'replies.user',
        ]);

        return [
            'supportTicket' => $supportTicket,
            'ticket' => $supportTicket,
        ];
    }

    public function name(): ?string
    {
        return "Тикет #{$this->supportTicket->id}";
    }

    public function description(): ?string
    {
        return 'Просмотр обращения и ответы поддержки.';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.support-tickets',
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Назад к списку')
                ->icon('bs.arrow-left')
                ->route('platform.support-tickets'),

            Button::make('Пометить отвеченным')
                ->icon('bs.check-circle')
                ->method('markAnswered')
                ->canSee($this->supportTicket->status !== SupportTicket::STATUS_ANSWERED),

            Button::make('Закрыть тикет')
                ->icon('bs.x-circle')
                ->method('markClosed')
                ->type(Color::DANGER)
                ->confirm('Закрыть этот тикет?')
                ->canSee($this->supportTicket->status !== SupportTicket::STATUS_CLOSED),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::view('orchid.support.ticket-detail'),
            Layout::block(SupportTicketReplyLayout::class)
                ->title('Ответить клиенту')
                ->description('Ответ будет сохранен в истории тикета. Для Telegram-тикетов сообщение также отправится пользователю.')
                ->commands(
                    Button::make('Отправить ответ')
                        ->type(Color::BASIC)
                        ->icon('bs.send')
                        ->method('reply')
                ),
        ];
    }

    public function reply(
        SupportTicket $supportTicket,
        Request $request,
        CustomerSupportService $supportService,
        SupportTicketReplyDeliveryService $deliveryService,
    ): RedirectResponse {
        $validated = $request->validate([
            'reply.message' => ['required', 'string', 'max:5000'],
        ]);

        $reply = $supportService->createReply(
            $supportTicket,
            $validated['reply']['message'],
            $request->user(),
        );

        $deliveryService->deliverToTelegram($reply);

        Toast::info('Ответ сохранен.');

        return redirect()->route('platform.support-tickets.view', $supportTicket);
    }

    public function markAnswered(SupportTicket $supportTicket, Request $request): RedirectResponse
    {
        $supportTicket->forceFill([
            'assigned_user_id' => $request->user()?->id ?? $supportTicket->assigned_user_id,
            'status' => SupportTicket::STATUS_ANSWERED,
            'answered_at' => $supportTicket->answered_at ?? now(),
            'closed_at' => null,
        ])->save();

        Toast::info('Тикет помечен как отвеченный.');

        return redirect()->route('platform.support-tickets.view', $supportTicket);
    }

    public function markClosed(SupportTicket $supportTicket, Request $request): RedirectResponse
    {
        $supportTicket->forceFill([
            'assigned_user_id' => $request->user()?->id ?? $supportTicket->assigned_user_id,
            'status' => SupportTicket::STATUS_CLOSED,
            'closed_at' => now(),
        ])->save();

        Toast::info('Тикет закрыт.');

        return redirect()->route('platform.support-tickets.view', $supportTicket);
    }
}
