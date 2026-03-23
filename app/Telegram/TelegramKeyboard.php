<?php

namespace App\Telegram;

class TelegramKeyboard
{
    public static function inline(array $keyboard): string
    {
        return json_encode([
            'inline_keyboard' => $keyboard,
        ], JSON_UNESCAPED_UNICODE);
    }

    public static function reply(array $keyboard, bool $isPersistent = false): string
    {
        return json_encode([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
            'is_persistent' => $isPersistent,
        ], JSON_UNESCAPED_UNICODE);
    }

    public static function mainMenu(): array
    {
        return [
            [
                ['text' => '🔑 Получить ключ', 'callback_data' => '/key'],
                ['text' => '📱 Инструкции по подключению', 'callback_data' => '/instructions'],
            ],
            [
                ['text' => '💳 Оплатить подписку', 'callback_data' => '/pay'],
                ['text' => '📊 Статус подписки', 'callback_data' => '/status'],
            ],
            [
                ['text' => '📝 Поддержка', 'callback_data' => '/support'],
                ['text' => '❓ Помощь', 'callback_data' => '/help'],
            ],
        ];
    }

    public static function backToMainMenu(string $text = '🏠 Главное меню'): array
    {
        return [[
            ['text' => $text, 'callback_data' => '/start'],
        ]];
    }

    public static function supportCancel(): array
    {
        return [[
            ['text' => '❌ Отмена', 'callback_data' => '/start'],
        ]];
    }

    public static function bottomMenuButton(): array
    {
        return [
            ['🏠 Меню'],
        ];
    }
}
