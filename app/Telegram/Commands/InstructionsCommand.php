<?php

namespace App\Telegram\Commands;

use App\Models\TelegramCommandLog;
use App\Telegram\TelegramKeyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class InstructionsCommand extends BaseCommand
{
    private const TYPE_ANDROID = 'android';

    private const TYPE_IPHONE = 'iphone';

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
        Telegram::sendMessage([
            'chat_id' => $this->customer->telegram_id,
            'text' => $this->getInstructions(),
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            'reply_markup' => $this->getInstructionsKeyboard(),
        ]);
    }

    private function getInstructions(): string
    {
        return match ($this->getInstructionType()) {
            self::TYPE_ANDROID => $this->getAndroidInstructions(),
            self::TYPE_IPHONE => $this->getIphoneInstructions(),
            default => $this->getPlatformSelectionText(),
        };
    }

    private function getInstructionsKeyboard(): string
    {
        return match ($this->getInstructionType()) {
            self::TYPE_ANDROID => TelegramKeyboard::inline([
                [['text' => '🍎 Инструкция для iPhone', 'callback_data' => '/instructions?type=iphone']],
                [['text' => '⬅️ Назад', 'callback_data' => '/start']],
            ]),
            self::TYPE_IPHONE => TelegramKeyboard::inline([
                [['text' => '🤖 Инструкция для Android', 'callback_data' => '/instructions?type=android']],
                [['text' => '⬅️ Назад', 'callback_data' => '/start']],
            ]),
            default => TelegramKeyboard::inline([
                [['text' => '🤖 Android', 'callback_data' => '/instructions?type=android']],
                [['text' => '🍎 iPhone', 'callback_data' => '/instructions?type=iphone']],
                [['text' => '⬅️ Назад', 'callback_data' => '/start']],
            ]),
        };
    }

    private function getInstructionType(): ?string
    {
        $type = strtolower((string) ($this->params['type'] ?? ''));

        return in_array($type, [self::TYPE_ANDROID, self::TYPE_IPHONE], true) ? $type : null;
    }

    private function getPlatformSelectionText(): string
    {
        return implode("\n", [
            '📱 <b>Инструкции по подключению</b>',
            '',
            'Выберите ваше устройство ниже, и я покажу подходящую инструкцию по подключению VPN.',
        ]);
    }

    private function getAndroidInstructions(): string
    {
        return implode("\n", [
            '📱 <b>Установка на Android</b>',
            '',
            '1. <b>Установите приложение</b>',
            'Скачайте <b>v2RayTun</b>:',
            '👉 <a href="https://play.google.com/store/apps/details?id=com.v2raytun.android">Google Play</a>',
            '👉 <a href="https://github.com/DigneZzZ/v2raytun/releases/download/5.19.64/v2RayTun_universal.apk">Скачать APK</a> <i>(если Google Play не работает)</i>',
            '',
            '2. <b>Добавьте подписку</b>',
            'Ваш VPN-ключ уже отправлен ботом.',
            'Скопируйте ключ из Telegram, откройте <b>v2RayTun</b> и добавьте его в приложение.',
            '',
            '3. <b>Подключитесь</b>',
            'Откройте приложение, выберите добавленный профиль и включите VPN.',
            '',
            '💡 <b>Если Android попросит разрешение на VPN</b>, просто подтвердите запрос.',
        ]);
    }

    private function getIphoneInstructions(): string
    {
        return implode("\n", [
            '🍎 <b>Установка на iPhone</b>',
            '',
            '1. <b>Установите приложение</b>',
            'Скачайте <b>Streisand</b>:',
            '👉 <a href="https://apps.apple.com/us/app/streisand/id6450534064">App Store</a>',
            '',
            '2. <b>Добавьте подписку</b>',
            'Ваш VPN-ключ уже отправлен ботом.',
            'Скопируйте ключ из Telegram, откройте <b>Streisand</b> и добавьте его в приложение.',
            '',
            '3. <b>Подключитесь</b>',
            'Откройте приложение, выберите добавленный профиль и включите VPN.',
            '',
            '💡 <b>Если iPhone попросит разрешение на VPN</b>, просто подтвердите запрос.',
        ]);
    }
}
