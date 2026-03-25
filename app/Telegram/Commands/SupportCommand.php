<?php

namespace App\Telegram\Commands;

use App\Models\CustomerPendingAction;
use App\Models\TelegramCommandLog;
use App\Telegram\TelegramKeyboard;
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
                  'Пожалуйста, опишите вашу проблему в одном сообщении:';

        Telegram::sendMessage([
            'chat_id' => $this->customer->telegram_id,
            'text' => $message,
            'parse_mode' => 'HTML',
            'reply_markup' => TelegramKeyboard::inline(TelegramKeyboard::supportCancel()),
        ]);
    }
}
