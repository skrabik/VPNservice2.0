<?php

namespace App\Telegram\Commands;

class CommandFactory
{
    private static array $command_map = [
        '/start' => StartCommand::class,
        '/key' => KeyCommand::class,
        '🔑 Получить ключ' => KeyCommand::class,
        '/pay' => PayCommand::class,
        '💳 Оплатить подписку' => PayCommand::class,
        '/status' => StatusCommand::class,
        '📊 Статус подписки' => StatusCommand::class,
        '/instructions' => InstructionsCommand::class,
        '📱 Инструкции по подключению' => InstructionsCommand::class,
        '/promo' => PromoCommand::class,
        '🎁 Ввести промокод' => PromoCommand::class,
        '/support' => SupportCommand::class,
        '📝 Поддержка' => SupportCommand::class,
    ];

    public static function create(string $command_name, array $params = []): ?string
    {
        return self::$command_map[$command_name] ?? HelpCommand::class;
    }

    public static function isPromoCode(string $command_name): bool
    {
        $promo_codes_json = env('TELEGRAM_PROMO_CODES', '[]');
        $promo_codes = json_decode($promo_codes_json, true);

        return is_array($promo_codes) && in_array($command_name, $promo_codes);
    }

    public static function getCommandClass(string $command_name): string
    {
        if (self::isPromoCode($command_name)) {
            return PromoCommand::class;
        }

        return self::create($command_name);
    }
}
