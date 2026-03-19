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
                  "Доступные команды:\n\n".
                  "🔑 /key - Получить ключ VPN\n".
                  "📱 /instructions - Инструкции по подключению к VPN\n".
                  "📊 /status - Проверить статус подписки\n".
                  "💳 /buy - Купить подписку\n".
                  "🎁 /promo - Ввести промокод\n".
                  "📝 /support - Создать тикет в поддержку\n".
                  'Нажмите на нужную команду ниже.';

        $keyboard = [
            [
                ['text' => '🔑 /key', 'callback_data' => '/key'],
                ['text' => '📱 /instructions', 'callback_data' => '/instructions'],
            ],
            [
                ['text' => '📊 /status', 'callback_data' => '/status'],
                ['text' => '💳 /buy', 'callback_data' => '/buy'],
            ],
            [
                ['text' => '🎁 /promo', 'callback_data' => '/promo'],
                ['text' => '📝 /support', 'callback_data' => '/support'],
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
