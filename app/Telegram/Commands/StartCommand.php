<?php

namespace App\Telegram\Commands;

use App\Models\TelegramCommandLog;
use App\Services\CustomerCabinetLinkService;
use App\Telegram\TelegramKeyboard;
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

        $keyboard = TelegramKeyboard::mainMenu();
        $claimUrl = (new CustomerCabinetLinkService)->getClaimUrl($this->customer);

        if ($claimUrl) {
            $keyboard[] = [['text' => '🌐 Открыть веб-кабинет', 'url' => $claimUrl]];
        }

        Telegram::sendMessage([
            'chat_id' => $this->customer->telegram_id,
            'text' => $message,
            'parse_mode' => 'HTML',
            'reply_markup' => TelegramKeyboard::inline($keyboard),
        ]);
    }
}
