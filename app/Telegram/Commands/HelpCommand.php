<?php

namespace App\Telegram\Commands;

use App\Models\Customer;
use App\Models\TelegramCommandLog;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class HelpCommand extends BaseCommand
{
    public function __construct(Update $update, Customer $customer, array $params)
    {
        parent::__construct($update, $customer, $params);
    }

    public function handle(): void
    {
        TelegramCommandLog::create([
            'customer_id' => $this->customer->id,
            'command_name' => 'Вызвал команду /help',
            'action' => 'Вызвал команду /help',
        ]);

        $message = "🤖 <b>VPN Бот - Помощь</b>\n\n".
                  "Доступные разделы:";

        $keyboard = [
            [
                ['text' => '🔑 Получить ключ', 'callback_data' => '/key'],
                ['text' => '📱 Инструкции', 'callback_data' => '/instructions'],
            ],
            [
                ['text' => '📊 Статус', 'callback_data' => '/status'],
                ['text' => '💳 Купить подписку', 'callback_data' => '/pay'],
            ],
            [
                // ['text' => '🎁 Промокод', 'callback_data' => '/promo'],
                ['text' => '📝 Поддержка', 'callback_data' => '/support'],
            ],
        ];

        Telegram::sendMessage([
            'chat_id' => $this->customer->telegram_id,
            'text' => $message,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard,
            ]),
        ]);
    }
}
