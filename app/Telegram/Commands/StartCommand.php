<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Laravel\Facades\Telegram;

class StartCommand extends BaseCommand
{
    public function handle(): void
    {
        $message = "Добро пожаловать! Я бот для управления VPN сервисом.\n\n".
                  "Доступные команды:\n".
                  "/pay - Оплатить подписку\n".
                  '/key - Получить ключ VPN';

        Telegram::sendMessage([
            'chat_id' => $this->customer->telegram_id,
            'text' => $message,
            'parse_mode' => 'HTML',
        ]);
    }
}
