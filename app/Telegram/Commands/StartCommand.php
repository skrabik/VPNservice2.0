<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Laravel\Facades\Telegram;

class StartCommand extends BaseCommand
{
    public function handle(): void
    {
        $message = "👋 Добро пожаловать в VPN сервис!\n\n".
            'Выберите нужную опцию:';

        $keyboard = [
            [['text' => '📊 Статус подписки', 'callback_data' => '/status']],
            [['text' => '🔑 Получить ключ VPN', 'callback_data' => '/key']],
            [['text' => '💳 Оплатить подписку', 'callback_data' => '/pay']],
            [['text' => '📱 Инструкции по подключению', 'callback_data' => '/instructions']],
            [['text' => '❓ Помощь', 'callback_data' => 'help']],
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
