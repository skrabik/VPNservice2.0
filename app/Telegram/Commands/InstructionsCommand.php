<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Laravel\Facades\Telegram;

class InstructionsCommand extends BaseCommand
{
    public function handle(): void
    {
        // Проверяем параметры из callback_data
        if (isset($this->params['instruction_type'])) {
            $this->sendSpecificInstruction($this->params['instruction_type']);

            return;
        }

        // Получаем текст сообщения для определения действия
        $messageText = $this->update->getMessage()->getText();

        // Определяем тип инструкции по тексту кнопки
        $instructionType = match ($messageText) {
            '📱 Outline (Shadowsocks)' => 'outline',
            '🔒 OpenVPN' => 'openvpn',
            '⚡ Xray (V2Ray)' => 'xray',
            '⬅️ Назад' => null,
            default => null
        };

        // Если это кнопка "Назад" или команда /instructions - показываем главное меню
        if ($instructionType === null) {
            $this->sendMainInstructions();

            return;
        }

        // Отправляем конкретную инструкцию
        $this->sendSpecificInstruction($instructionType);
    }

    private function sendMainInstructions(): void
    {
        $message = "🔧 <b>Инструкции по подключению к VPN</b>\n\n".
            'Выберите ваш протокол для получения подробных инструкций:';

        $keyboard = [
            [
                ['text' => '📱 Outline (Shadowsocks)', 'callback_data' => '/instructions?instruction_type=outline'],
                ['text' => '🔒 OpenVPN', 'callback_data' => '/instructions?instruction_type=openvpn'],
            ],
            [
                ['text' => '⚡ Xray (V2Ray)', 'callback_data' => '/instructions?instruction_type=xray'],
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => '/help'],
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

    private function sendSpecificInstruction(string $type): void
    {
        $instruction = match ($type) {
            'outline' => $this->getOutlineInstructions(),
            'openvpn' => $this->getOpenVPNInstructions(),
            'xray' => $this->getXrayInstructions(),
            default => $this->getMainInstructions()
        };

        $keyboard = [
            ['⬅️ Назад'],
        ];

        Telegram::sendMessage([
            'chat_id' => $this->customer->telegram_id,
            'text' => $instruction,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            'reply_markup' => json_encode([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => false,
            ]),
        ]);
    }

    private function getOutlineInstructions(): string
    {
        return "📱 <b>Инструкция по подключению к Outline (Shadowsocks)</b>\n\n".
            "🔹 <b>Шаг 1: Скачивание приложения</b>\n".
            "• Android: Скачайте Outline Client из Google Play\n".
            "• iOS: Скачайте Outline Client из App Store\n".
            "• Windows/Mac: Скачайте с официального сайта outline.org\n\n".

            "🔹 <b>Шаг 2: Добавление ключа</b>\n".
            "• Откройте приложение Outline Client\n".
            "• Нажмите кнопку \"+\" для добавления сервера\n".
            "• Отсканируйте QR-код или введите ключ вручную\n".
            "• Нажмите \"Добавить сервер\"\n\n".

            "🔹 <b>Шаг 3: Подключение</b>\n".
            "• Выберите добавленный сервер\n".
            "• Нажмите кнопку подключения (▶️)\n".
            "• Дождитесь установки соединения\n\n".

            "💡 <b>Советы:</b>\n".
            "• Убедитесь, что ключ скопирован полностью\n".
            "• При проблемах попробуйте перезапустить приложение\n".
            '• Для получения нового ключа используйте /key';
    }

    private function getOpenVPNInstructions(): string
    {
        return "🔒 <b>Инструкция по подключению к OpenVPN</b>\n\n".
            "🔹 <b>Шаг 1: Скачивание приложения</b>\n".
            "• Android: Скачайте OpenVPN Connect из Google Play\n".
            "• iOS: Скачайте OpenVPN Connect из App Store\n".
            "• Windows/Mac: Скачайте с openvpn.net\n\n".

            "🔹 <b>Шаг 2: Импорт конфигурации</b>\n".
            "• Откройте приложение OpenVPN Connect\n".
            "• Нажмите \"Импорт\" или \"+\"\n".
            "• Выберите .ovpn файл или введите URL\n".
            "• Нажмите \"Импорт\"\n\n".

            "🔹 <b>Шаг 3: Настройка учетных данных</b>\n".
            "• Введите логин и пароль от вашего аккаунта\n".
            "• Сохраните учетные данные\n".
            "• Нажмите \"Подключиться\"\n\n".

            "🔹 <b>Шаг 4: Подключение</b>\n".
            "• Дождитесь установки VPN соединения\n".
            "• Проверьте статус подключения\n\n".

            "💡 <b>Советы:</b>\n".
            "• Убедитесь, что .ovpn файл не поврежден\n".
            "• При ошибках проверьте логин и пароль\n".
            '• Для получения конфигурации используйте /key';
    }

    private function getXrayInstructions(): string
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

    private function getMainInstructions(): string
    {
        return "🔧 <b>Инструкции по подключению к VPN</b>\n\n".
            "Выберите ваш протокол для получения подробных инструкций:\n\n".
            "📱 <b>Outline (Shadowsocks)</b>\n".
            "• Скачайте приложение Outline Client\n".
            "• Добавьте ключ через QR-код или вручную\n".
            "• Нажмите кнопку подключения\n\n".

            "🔒 <b>OpenVPN</b>\n".
            "• Скачайте OpenVPN Connect\n".
            "• Импортируйте .ovpn файл\n".
            "• Введите логин и пароль\n".
            "• Подключитесь к серверу\n\n".

            "⚡ <b>Xray (V2Ray)</b>\n".
            "• Скачайте V2RayNG (Android) или V2RayX (iOS)\n".
            "• Добавьте конфигурацию сервера\n".
            "• Выберите протокол (VMess, VLESS, Trojan)\n".
            "• Нажмите кнопку подключения\n\n".

            "💡 <b>Полезные советы:</b>\n".
            "• Убедитесь, что у вас стабильное интернет-соединение\n".
            "• При проблемах с подключением попробуйте другой сервер\n".
            "• Для получения ключа используйте команду /key\n".
            "• Для оплаты используйте команду /pay\n\n".

            '❓ Если у вас возникли вопросы, обратитесь в поддержку.';
    }
}
