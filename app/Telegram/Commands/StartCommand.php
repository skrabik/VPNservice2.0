<?php

namespace App\Telegram\Commands;

use App\Models\TelegramCommandLog;
use App\Services\CustomerAuthLinkService;
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

        $message = "👋 Добро пожаловать в NerpaVPN!\n\n".
            'Выберите нужную опцию:';

        $keyboard = TelegramKeyboard::mainMenu();
        $cabinetLinkService = new CustomerCabinetLinkService;

        $keyboard[] = [[
            'text' => '🌐 Открыть веб-кабинет',
            'web_app' => ['url' => $cabinetLinkService->getMiniAppUrl()],
        ]];

        $keyboard[] = [[
            'text' => '🔐 Войти в кабинет в браузере',
            'url' => app(CustomerAuthLinkService::class)->createBrowserLoginUrl($this->customer),
        ]];

        Telegram::sendMessage([
            'chat_id' => $this->customer->telegram_id,
            'text' => $message,
            'parse_mode' => 'HTML',
            'reply_markup' => TelegramKeyboard::inline($keyboard),
        ]);
    }
}
