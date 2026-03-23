<?php

namespace App\Services;

class CustomerInstructionService
{
    public const TYPE_ANDROID = 'android';
    public const TYPE_IPHONE = 'iphone';
    public const TYPE_WINDOWS = 'windows';
    public const TYPE_MACOS = 'macos';

    public function normalizeType(?string $type): ?string
    {
        $type = strtolower((string) $type);

        return in_array($type, [
            self::TYPE_ANDROID,
            self::TYPE_IPHONE,
            self::TYPE_WINDOWS,
            self::TYPE_MACOS,
        ], true) ? $type : null;
    }

    public function getPlatforms(): array
    {
        return [
            [
                'type' => self::TYPE_ANDROID,
                'label' => 'Android',
                'icon' => '🤖',
                'download_url' => 'https:/ёps/details?id=com.v2raytun.android',
            ],
            [
                'type' => self::TYPE_IPHONE,
                'label' => 'iPhone',
                'icon' => '🍎',
                'download_url' => 'https://apps.apple.com/us/app/streisand/id6450534064',
            ],
            [
                'type' => self::TYPE_WINDOWS,
                'label' => 'Windows',
                'icon' => '🪟',
                'download_url' => 'https://storage.v2raytun.com/v2RayTun_Setup.exe',
            ],
            [
                'type' => self::TYPE_MACOS,
                'label' => 'macOS',
                'icon' => '🖥️',
                'download_url' => 'https://apps.apple.com/en/app/v2raytun/id6476628951',
            ],
        ];
    }

    public function getInstructions(?string $type): string
    {
        return match ($this->normalizeType($type)) {
            self::TYPE_ANDROID => $this->getAndroidInstructions(),
            self::TYPE_IPHONE => $this->getIphoneInstructions(),
            self::TYPE_WINDOWS => $this->getWindowsInstructions(),
            self::TYPE_MACOS => $this->getMacosInstructions(),
            default => $this->getPlatformSelectionText(),
        };
    }

    public function getPlatformSelectionText(): string
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
            'Ваш VPN-ключ доступен в Telegram и веб-кабинете.',
            'Скопируйте ключ, откройте <b>v2RayTun</b> и добавьте его в приложение.',
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
            'Ваш VPN-ключ доступен в Telegram и веб-кабинете.',
            'Скопируйте ключ, откройте <b>Streisand</b> и добавьте его в приложение.',
            '',
            '3. <b>Подключитесь</b>',
            'Откройте приложение, выберите добавленный профиль и включите VPN.',
            '',
            '💡 <b>Если iPhone попросит разрешение на VPN</b>, просто подтвердите запрос.',
        ]);
    }

    private function getWindowsInstructions(): string
    {
        return implode("\n", [
            '🪟 <b>Установка на Windows</b>',
            '',
            '1. <b>Установите приложение</b>',
            'Скачайте <b>v2RayTun</b>:',
            '👉 <a href="https://storage.v2raytun.com/v2RayTun_Setup.exe">Скачать для Windows</a>',
            '',
            '2. <b>Добавьте подписку</b>',
            'Ваш VPN-ключ доступен в Telegram и веб-кабинете.',
            'Скопируйте ключ, откройте <b>v2RayTun</b> и импортируйте его в приложение.',
            '',
            '3. <b>Подключитесь</b>',
            'Выберите добавленный профиль и нажмите кнопку подключения.',
            '',
            '💡 <b>Если Windows попросит разрешение</b>, подтвердите установку VPN-компонента или сетевого адаптера.',
        ]);
    }

    private function getMacosInstructions(): string
    {
        return implode("\n", [
            '🖥️ <b>Установка на macOS</b>',
            '',
            '1. <b>Установите приложение</b>',
            'Скачайте <b>v2RayTun</b>:',
            '👉 <a href="https://apps.apple.com/en/app/v2raytun/id6476628951">Mac App Store</a>',
            '',
            '2. <b>Добавьте подписку</b>',
            'Ваш VPN-ключ доступен в Telegram и веб-кабинете.',
            'Скопируйте ключ, откройте <b>v2RayTun</b> и импортируйте его в приложение.',
            '',
            '3. <b>Подключитесь</b>',
            'Выберите добавленный профиль и включите VPN.',
            '',
            '💡 <b>Если macOS попросит разрешение на VPN</b>, просто подтвердите запрос в системе.',
        ]);
    }
}
