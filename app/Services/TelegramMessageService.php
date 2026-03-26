<?php

namespace App\Services;

use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramMessageService
{
    public function sendText(string $telegramId, string $text): void
    {
        Telegram::sendMessage([
            'chat_id' => $telegramId,
            'text' => $text,
        ]);
    }
}
