<?php

namespace App\Telegram\Commands;

use App\Models\Customer;
use App\Models\TelegramCommandLog;
use App\Telegram\TelegramKeyboard;
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

        Telegram::sendMessage([
            'chat_id' => $this->customer->telegram_id,
            'text' => $message,
            'parse_mode' => 'HTML',
            'reply_markup' => TelegramKeyboard::inline(TelegramKeyboard::mainMenu()),
        ]);
    }
}
