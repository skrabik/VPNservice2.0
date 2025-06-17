<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Laravel\Facades\Telegram;

class StartCommand extends BaseCommand
{
    public function handle(): void
    {
        $message = "Добро пожаловать! Я бот для управления VPN сервисом.\n\n".
                  "Доступные команды:\n".
                  "💳&nbsp;/buy - Купить подписку\n".
                  '🔑&nbsp;/key - Получить ключ VPN';

        $keyboard = [
            ['💳 Купить подписку'],
            ['🔑 Получить ключ', '❓ Помощь'],
            ['⬅️ Назад'],
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
