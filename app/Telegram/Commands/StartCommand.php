<?php

namespace App\Telegram\Commands;

use App\Models\TelegramCommandLog;
use Telegram\Bot\Laravel\Facades\Telegram;

class StartCommand extends BaseCommand
{
    public function handle(): void
    {
        TelegramCommandLog::create([
            'customer_id' => $this->customer->id,
            'command_name' => 'start',
            'action' => 'start',
        ]);

        $message = "👋 Добро пожаловать в VPN сервис!\n\n".
            'Выберите нужную опцию:';

        $keyboard = [
            ['🔑 Получить ключ', '📱 Инструкции по подключению'],
            ['💳 Оплатить подписку', '📊 Статус подписки'],
            ['🎁 Ввести промокод', '❓ Помощь'],
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
