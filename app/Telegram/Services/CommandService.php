<?php

namespace App\Telegram\Services;

use App\Models\Customer;
use App\Telegram\Commands;
use App\Telegram\TelegramManager;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Objects\Update;

class CommandService
{
    public static function process(Update $update, Customer $customer): void
    {
        extract(TelegramManager::parseMessageData($update));

        $command_class = self::getCommandClass($command_name);

        $command = new $command_class($update, $customer, $params);

        try {
            $command->handle();
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());

            return;
        }
    }

    private static function getCommandClass($command_name): string
    {
        // TODO пока для тестирования, позже вынести промокоды в отдельную сущность
        if (in_array($command_name, self::getPromoCodes())) {
            return Commands\PromoCommand::class;
        }

        return match ($command_name) {
            '/start' => Commands\StartCommand::class,
            '/key', '🔑 Получить ключ' => Commands\KeyCommand::class,
            '/pay', '💳 Оплатить подписку' => Commands\PayCommand::class,
            '/status', '📊 Статус подписки' => Commands\StatusCommand::class,
            '/instructions', '📱 Инструкции по подключению' => Commands\InstructionsCommand::class,
            '/promo', '🎁 Ввести промокод' => Commands\PromoCommand::class,
            '/support', '📝 Поддержка' => Commands\SupportCommand::class,
            default => Commands\HelpCommand::class,
        };
    }

    private static function getPromoCodes(): array
    {
        $promo_codes_json = env('TELEGRAM_PROMO_CODES', '[]');
        $promo_codes = json_decode($promo_codes_json, true);

        return is_array($promo_codes) ? $promo_codes : [];
    }
}
