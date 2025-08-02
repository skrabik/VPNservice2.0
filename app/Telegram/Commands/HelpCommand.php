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
                  'Если у вас возникли вопросы, используйте команду /support.';

        $keyboard = [
            ['🔑 Получить ключ', '📱 Инструкции по подключению'],
            ['💳 Оплатить подписку', '📊 Статус подписки'],
            ['🎁 Ввести промокод', '📝 Поддержка'],
            ['❓ Помощь'],
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
