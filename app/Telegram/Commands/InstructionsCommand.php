<?php

namespace App\Telegram\Commands;

use App\Models\TelegramCommandLog;
use Telegram\Bot\Laravel\Facades\Telegram;

class InstructionsCommand extends BaseCommand
{
    public function handle(): void
    {
        TelegramCommandLog::create([
            'customer_id' => $this->customer->id,
            'command_name' => 'Вызвал команду /instructions',
            'action' => 'Вызвал команду /instructions',
        ]);

        $this->sendInstructions();
    }

    private function sendInstructions(): void
    {
        $keyboard = [
            ['⬅️ Назад'],
        ];

        Telegram::sendMessage([
            'chat_id' => $this->customer->telegram_id,
            'text' => $this->getInstructions(),
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            'reply_markup' => json_encode([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => false,
            ]),
        ]);
    }

    private function getInstructions(): string
    {
        return "⚡ <b>Инструкция по подключению к Xray (V2Ray)</b>\n\n".
            "🔹 <b>Шаг 1: Скачивание приложения</b>\n".
            "• Android: Скачайте V2RayNG из Google Play\n".
            "• iOS: Скачайте V2RayX из App Store\n".
            "• Windows: Скачайте V2RayN с GitHub\n".
            "• Mac: Скачайте V2RayX с GitHub\n\n".

            "🔹 <b>Шаг 2: Добавление сервера</b>\n".
            "• Откройте приложение V2RayNG/V2RayX\n".
            "• Нажмите \"+\" для добавления сервера\n".
            "• Выберите способ импорта:\n".
            "  - QR-код\n".
            "  - Ссылка (vmess://, vless://, trojan://)\n".
            "  - Ручной ввод\n\n".

            "🔹 <b>Шаг 3: Выбор протокола</b>\n".
            "• VMess: Универсальный протокол\n".
            "• VLESS: Легковесный протокол\n".
            "• Trojan: Протокол с TLS шифрованием\n".
            "• Выберите наиболее подходящий\n\n".

            "🔹 <b>Шаг 4: Подключение</b>\n".
            "• Выберите добавленный сервер\n".
            "• Нажмите кнопку подключения\n".
            "• Дождитесь установки соединения\n\n".

            "💡 <b>Советы:</b>\n".
            "• VMess рекомендуется для большинства случаев\n".
            "• VLESS быстрее, но менее совместим\n".
            "• Trojan лучше обходит блокировки\n".
            '• Для получения конфигурации используйте /key';
    }
}
