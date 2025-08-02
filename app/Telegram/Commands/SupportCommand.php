<?php

namespace App\Telegram\Commands;

use App\Models\CustomerPendingAction;
use App\Models\TelegramCommandLog;
use Telegram\Bot\Laravel\Facades\Telegram;

class SupportCommand extends BaseCommand
{
    public function handle(): void
    {
        TelegramCommandLog::create([
            'customer_id' => $this->customer->id,
            'command_name' => 'support',
            'action' => 'Создание тикета поддержки',
        ]);

        $this->customer->pending_actions()->create([
            'action_id' => CustomerPendingAction::ACTION_SUPPORT_TICKET_TYPE,
        ]);

        $message = "📝 <b>Создание тикета поддержки</b>\n\n".
                  'Пожалуйста, введите ваше сообщение:';

        $keyboard = [
            ['❌ Отмена'],
        ];

        Telegram::sendMessage([
            'chat_id' => $this->customer->telegram_id,
            'text' => $message,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => false,
            ]),
        ]);
    }
}
